<?php

namespace App\Domain\Housekeeping;

use App\Infrastructure\Persistence\HousekeepingStatusHistory;
use App\Infrastructure\Persistence\HousekeepingTask;
use App\Infrastructure\Persistence\Property;
use App\Infrastructure\Persistence\Room;
use App\Infrastructure\Persistence\Stay;
use App\Infrastructure\Persistence\User;
use Illuminate\Support\Facades\DB;

class HousekeepingService
{
    /**
     * Update room status and append audit history record.
     */
    public function updateRoomStatus(Room $room, string $newStatus, ?User $staff = null, ?string $notes = null, string $source = 'staff'): Room
    {
        return DB::transaction(function () use ($room, $newStatus, $staff, $notes, $source) {
            $fromStatus = $room->status;
            
            if ($fromStatus === $newStatus) {
                return $room;
            }

            $room->update(['status' => $newStatus]);

            HousekeepingStatusHistory::create([
                'room_id'     => $room->id,
                'property_id' => $room->property_id,
                'from_status' => $fromStatus,
                'to_status'   => $newStatus,
                'changed_by'  => $staff?->id ?: auth()->id(),
                'source'      => $source,
                'notes'       => $notes,
                'changed_at'  => now(),
            ]);

            return $room;
        });
    }

    /**
     * Inspector sign-off transition (clean -> inspected).
     */
    public function inspectorSignOff(Room $room, ?User $inspector = null, ?string $notes = null): Room
    {
        return $this->updateRoomStatus($room, 'inspected', $inspector, $notes ?: 'Inspector sign-off verified', 'staff');
    }

    /**
     * Assign housekeeping task to staff.
     */
    public function assignTask(HousekeepingTask $task, User $housekeeper, ?User $assignedBy = null): HousekeepingTask
    {
        $task->update([
            'assigned_to' => $housekeeper->id,
            'assigned_by' => $assignedBy?->id ?: auth()->id(),
            'status'      => $task->status === 'pending' ? 'in_progress' : $task->status,
            'started_at'  => $task->started_at ?: now(),
        ]);

        return $task;
    }

    /**
     * Start housekeeping task.
     */
    public function startTask(HousekeepingTask $task): HousekeepingTask
    {
        $task->update([
            'status'     => 'in_progress',
            'started_at' => now(),
        ]);

        if ($task->room && $task->room->status === 'dirty') {
            $this->updateRoomStatus($task->room, 'cleaning', auth()->user(), 'Cleaning in progress', 'pms');
        }

        return $task;
    }

    /**
     * Complete task and auto-update room to clean status.
     */
    public function completeTask(HousekeepingTask $task, ?User $staff = null): HousekeepingTask
    {
        return DB::transaction(function () use ($task, $staff) {
            $task->update([
                'status'       => 'completed',
                'completed_at' => now(),
            ]);

            if ($task->room) {
                $this->updateRoomStatus($task->room, 'clean', $staff, 'Housekeeping task #' . $task->id . ' completed', 'staff');
            }

            return $task;
        });
    }

    /**
     * Auto-generate daily tasks for departures and stayovers.
     */
    public function generateDailyTasks(Property $property, ?string $date = null): int
    {
        $targetDate = $date ?: now()->toDateString();
        $tasksCreated = 0;

        // 1. Departures today -> checkout_clean
        $departingStays = Stay::where('property_id', $property->id)
            ->where('departure_date', $targetDate)
            ->whereNotNull('room_id')
            ->get();

        foreach ($departingStays as $stay) {
            $exists = HousekeepingTask::where('property_id', $property->id)
                ->where('room_id', $stay->room_id)
                ->where('due_date', $targetDate)
                ->where('type', 'checkout_clean')
                ->exists();

            if (!$exists) {
                HousekeepingTask::create([
                    'property_id' => $property->id,
                    'room_id'     => $stay->room_id,
                    'type'        => 'checkout_clean',
                    'status'      => 'pending',
                    'priority'    => 'high',
                    'due_date'    => $targetDate,
                    'notes'       => 'Guest checkout cleaning required',
                ]);
                $tasksCreated++;
            }
        }

        // 2. In-house stayovers -> stayover_clean
        $stayoverStays = Stay::where('property_id', $property->id)
            ->where('status', 'checked_in')
            ->where('arrival_date', '<', $targetDate)
            ->where('departure_date', '>', $targetDate)
            ->whereNotNull('room_id')
            ->get();

        foreach ($stayoverStays as $stay) {
            $exists = HousekeepingTask::where('property_id', $property->id)
                ->where('room_id', $stay->room_id)
                ->where('due_date', $targetDate)
                ->where('type', 'stayover_clean')
                ->exists();

            if (!$exists) {
                HousekeepingTask::create([
                    'property_id' => $property->id,
                    'room_id'     => $stay->room_id,
                    'type'        => 'stayover_clean',
                    'status'      => 'pending',
                    'priority'    => 'normal',
                    'due_date'    => $targetDate,
                    'notes'       => 'Occupied stayover daily service',
                ]);
                $tasksCreated++;
            }
        }

        return $tasksCreated;
    }
}
