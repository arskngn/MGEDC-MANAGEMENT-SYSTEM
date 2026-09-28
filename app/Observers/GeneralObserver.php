<?php

namespace App\Observers;

use App\Events\ModelChanged;
use Illuminate\Database\Eloquent\Model;

class GeneralObserver
{
    /**
     * Handle the Model "created" event.
     */
    public function created(Model $model): void
    {
        event(new ModelChanged('created', $model, $model->toArray()));
    }

    /**
     * Handle the Model "updated" event.
     */
    public function updated(Model $model): void
    {
        $changes = $model->getChanges();
        // Don't log if only timestamps changed
        unset($changes['updated_at']);
        
        if (!empty($changes)) {
            $audit = [];
            foreach ($changes as $key => $value) {
                $audit[$key] = [
                    'old' => $model->getOriginal($key),
                    'new' => $value
                ];
            }
            event(new ModelChanged('updated', $model, $audit));
        }
    }

    /**
     * Handle the Model "deleted" event.
     */
    public function deleted(Model $model): void
    {
        // Use ShouldBroadcastNow-style direct dispatch — avoid SerializesModels 
        // which would fail to restore a deleted model from the DB.
        \Illuminate\Support\Facades\Broadcast::on(new \Illuminate\Broadcasting\Channel('system-updates'))
            ->as('model.changed')
            ->with([
                'action'    => 'deleted',
                'model'     => class_basename($model),
                'id'        => $model->getKey(),
                'changes'   => null,
                'timestamp' => now()->toDateTimeString(),
            ])
            ->sendNow();
    }
}
