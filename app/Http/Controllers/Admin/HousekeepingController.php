<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Housekeeping\HousekeepingService;
use App\Http\Controllers\Controller;
use App\Infrastructure\Persistence\HousekeepingTask;
use App\Infrastructure\Persistence\MaintenanceRequest;
use App\Infrastructure\Persistence\Property;
use App\Infrastructure\Persistence\Room;
use App\Infrastructure\Persistence\User;
use Illuminate\Http\Request;

class HousekeepingController extends Controller
{
    protected HousekeepingService $housekeepingService;

    public function __construct(HousekeepingService $housekeepingService)
    {
        $this->middleware('auth');
        $this->housekeepingService = $housekeepingService;
    }

    protected function resolveCurrentProperty(): ?Property
    {
        return app()->bound('current.property') ? app('current.property') : Property::first();
    }

    /**
     * Housekeeping Kanban & Management Board
     */
    public function index(Request $request)
    {
        $property = $this->resolveCurrentProperty();

        if ($property) {
            // Auto generate daily cleaning tasks for today if needed
            $this->housekeepingService->generateDailyTasks($property, now()->toDateString());
        }

        $rooms = $property ? Room::where('property_id', $property->id)
            ->with(['roomType', 'building', 'floor'])
            ->get() : collect();

        $tasks = $property ? HousekeepingTask::where('property_id', $property->id)
            ->whereDate('due_date', now()->toDateString())
            ->with(['room', 'assignedTo', 'assignedBy'])
            ->latest()
            ->get() : collect();

        $maintenanceRequests = $property ? MaintenanceRequest::where('property_id', $property->id)
            ->with(['room', 'reportedBy', 'assignedTo'])
            ->latest()
            ->get() : collect();

        $staffMembers = $property ? User::all() : collect();

        return view('admin.modules.housekeeping', compact(
            'property', 'rooms', 'tasks', 'maintenanceRequests', 'staffMembers'
        ));
    }

    /**
     * Update room housekeeping status
     */
    public function updateRoomStatus(Request $request, Room $room)
    {
        $request->validate([
            'status' => 'required|string|in:dirty,cleaning,clean,inspected,out_of_order,out_of_service',
            'notes'  => 'nullable|string',
        ]);

        try {
            $this->housekeepingService->updateRoomStatus(
                $room,
                $request->input('status'),
                auth()->user(),
                $request->input('notes')
            );

            if ($request->wantsJson()) {
                return response()->json(['success' => true, 'message' => "Room {$room->room_number} status updated to " . ucfirst($request->input('status'))]);
            }

            return redirect()->back()->with('success', "Room {$room->room_number} status updated successfully.");
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }
            return redirect()->back()->with('error', 'Status update failed: ' . $e->getMessage());
        }
    }

    /**
     * Inspector sign-off room
     */
    public function signOff(Request $request, Room $room)
    {
        try {
            $this->housekeepingService->inspectorSignOff($room, auth()->user(), $request->input('notes'));

            if ($request->wantsJson()) {
                return response()->json(['success' => true, 'message' => "Room {$room->room_number} inspected & approved."]);
            }

            return redirect()->back()->with('success', "Room {$room->room_number} passed inspection.");
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }
            return redirect()->back()->with('error', 'Inspection sign-off failed: ' . $e->getMessage());
        }
    }

    /**
     * Assign housekeeping task
     */
    public function assignTask(Request $request, HousekeepingTask $task)
    {
        $request->validate([
            'assigned_to' => 'required|exists:users,id',
        ]);

        $housekeeper = User::findOrFail($request->input('assigned_to'));

        try {
            $this->housekeepingService->assignTask($task, $housekeeper, auth()->user());
            return redirect()->back()->with('success', "Task assigned to {$housekeeper->name}.");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Task assignment failed: ' . $e->getMessage());
        }
    }

    /**
     * Complete housekeeping task
     */
    public function completeTask(Request $request, HousekeepingTask $task)
    {
        try {
            $this->housekeepingService->completeTask($task, auth()->user());

            if ($request->wantsJson()) {
                return response()->json(['success' => true, 'message' => 'Housekeeping task marked completed.']);
            }

            return redirect()->back()->with('success', 'Task completed successfully.');
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }
            return redirect()->back()->with('error', 'Task completion failed: ' . $e->getMessage());
        }
    }

    /**
     * Log new maintenance request
     */
    public function storeMaintenance(Request $request)
    {
        $property = $this->resolveCurrentProperty();

        $request->validate([
            'room_id'     => 'nullable|exists:rooms,id',
            'category'    => 'required|string',
            'priority'    => 'required|string|in:urgent,high,normal,low',
            'description' => 'required|string',
        ]);

        try {
            MaintenanceRequest::create([
                'property_id' => $property->id,
                'room_id'     => $request->input('room_id'),
                'category'    => $request->input('category'),
                'priority'    => $request->input('priority'),
                'status'      => 'open',
                'description' => $request->input('description'),
                'reported_by' => auth()->id(),
            ]);

            // If room attached and high/urgent priority, auto-mark room out_of_order
            if ($request->input('room_id') && in_array($request->input('priority'), ['urgent', 'high'])) {
                $room = Room::find($request->input('room_id'));
                if ($room) {
                    $this->housekeepingService->updateRoomStatus($room, 'out_of_order', auth()->user(), 'Maintenance request logged');
                }
            }

            return redirect()->back()->with('success', 'Maintenance request logged successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to log maintenance: ' . $e->getMessage());
        }
    }

    /**
     * Update maintenance request status
     */
    public function updateMaintenance(Request $request, MaintenanceRequest $maintenance)
    {
        $request->validate([
            'status'           => 'required|string|in:open,in_progress,completed,deferred',
            'resolution_notes' => 'nullable|string',
        ]);

        try {
            $data = [
                'status'           => $request->input('status'),
                'resolution_notes' => $request->input('resolution_notes'),
            ];

            if ($request->input('status') === 'completed') {
                $data['completed_at'] = now();

                // If room was out_of_order, set back to dirty for cleaning
                if ($maintenance->room && $maintenance->room->status === 'out_of_order') {
                    $this->housekeepingService->updateRoomStatus($maintenance->room, 'dirty', auth()->user(), 'Maintenance completed');
                }
            }

            $maintenance->update($data);

            return redirect()->back()->with('success', 'Maintenance request updated.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Update failed: ' . $e->getMessage());
        }
    }
}
