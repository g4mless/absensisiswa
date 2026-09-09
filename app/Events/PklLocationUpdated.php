<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PklLocationUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public int $studentPklId,
        public int $studentId,
        public float|string $latitude,
        public float|string $longitude,
        public float|string|null $accuracy = null,
        public ?string $recordedAt = null,
    ) {
        $this->recordedAt ??= now()->toDateTimeString();
    }

    /**
     * Channel: pkl.student.{studentPklId}
     * Jika studentPklId null/0 (siswa PKL tanpa record ACTIVE),
     * fallback ke pkl.student.s{studentId} agar supervisor tetap bisa subscribe.
     */
    public function broadcastOn(): Channel
    {
        $key = $this->studentPklId ?: 's'.$this->studentId;

        return new Channel('pkl.student.'.$key);
    }

    public function broadcastAs(): string
    {
        return 'pkl.location.updated';
    }

    public function broadcastWith(): array
    {
        return [
            'student_pkl_id' => $this->studentPklId,
            'student_id' => $this->studentId,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'accuracy' => $this->accuracy,
            'recorded_at' => $this->recordedAt,
        ];
    }
}
