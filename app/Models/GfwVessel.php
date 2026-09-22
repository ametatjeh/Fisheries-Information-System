<?php

namespace App\Models;

use App\Models\Gfw\GfwVessel as BaseGfwVessel;

class GfwVessel extends BaseGfwVessel
{
    /**
     * Get the current connection name for the model.
     *
     * @return string|null
     */
    public function getConnectionName()
    {
        if (app()->runningUnitTests() && config('gfw.testing_use_default_db', true)) {
            return config('database.default');
        }

        return parent::getConnectionName();
    }
}
