<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Integrations\ChannelManagerService;
use App\Http\Controllers\Controller;
use App\Infrastructure\Persistence\DeadLetterItem;
use Illuminate\Http\Request;

class DeadLetterQueueController extends Controller
{
    protected ChannelManagerService $channelService;

    public function __construct(ChannelManagerService $channelService)
    {
        $this->middleware('auth');
        $this->channelService = $channelService;
    }

    /**
     * Dead-Letter Queue Dashboard Roster.
     */
    public function index(Request $request)
    {
        $statusFilter = $request->input('status', 'all');

        $query = DeadLetterItem::with('resolvedBy')->latest();

        if ($statusFilter !== 'all') {
            $query->where('status', $statusFilter);
        }

        $deadLetterItems = $query->paginate(20);

        return view('admin.modules.dead_letter', compact('deadLetterItems', 'statusFilter'));
    }

    /**
     * Replay dead-letter item payload.
     */
    public function replay(Request $request, DeadLetterItem $item)
    {
        try {
            $this->channelService->replayDeadLetterItem($item, auth()->user());
            return redirect()->back()->with('success', "Item #{$item->id} replayed and resolved successfully.");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', "Replay failed for Item #{$item->id}: " . $e->getMessage());
        }
    }

    /**
     * Discard dead-letter item.
     */
    public function discard(Request $request, DeadLetterItem $item)
    {
        try {
            $item->update([
                'status'      => 'discarded',
                'resolved_by' => auth()->id(),
                'resolved_at' => now(),
                'notes'       => 'Discarded by administrator',
            ]);

            return redirect()->back()->with('success', "Item #{$item->id} marked as discarded.");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Discard failed: ' . $e->getMessage());
        }
    }
}
