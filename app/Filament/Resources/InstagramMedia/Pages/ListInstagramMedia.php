<?php

namespace App\Filament\Resources\InstagramMedia\Pages;

use App\Filament\Resources\InstagramMedia\InstagramMediaResource;
use Filament\Resources\Pages\ListRecords;

class ListInstagramMedia extends ListRecords
{
    protected static string $resource = InstagramMediaResource::class;

    public function bootedInteractsWithTable(): void
    {
        $groupingBeforeParent = $this->tableGrouping;

        parent::bootedInteractsWithTable();

        $defaultGroup = $this->getTable()->getDefaultGroup();

        if ($defaultGroup === null) {
            return;
        }

        $defaultGroupId = $defaultGroup->getId();

        if (blank($groupingBeforeParent)) {
            $this->tableGrouping = "{$defaultGroupId}:desc";

            return;
        }

        if (
            $this->tableGrouping === "{$defaultGroupId}:asc"
            && str($groupingBeforeParent)->endsWith(':desc')
        ) {
            $this->tableGrouping = $groupingBeforeParent;
        }
    }
}
