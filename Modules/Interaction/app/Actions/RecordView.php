<?php

namespace Modules\Interaction\Actions;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Modules\Interaction\Models\View;

class RecordView
{
    public function handle(Request $request, Model $viewable): View
    {
        $identity = $request->user()?->getAuthIdentifier()
            ?? implode('|', [$request->ip(), $request->userAgent()]);

        $visitorHash = hash_hmac('sha256', (string) $identity, (string) config('app.key'));

        return $viewable->views()->firstOrCreate(
            [
                'visitor_hash' => $visitorHash,
                'viewed_on' => today(),
            ],
            ['user_id' => $request->user()?->getAuthIdentifier()],
        );
    }
}
