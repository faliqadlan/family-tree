<?php

namespace App\Filament\Resources\StubProfileResource\Pages;

use App\Filament\Resources\StubProfileResource;
use Filament\Resources\Pages\CreateRecord;

class CreateStubProfile extends CreateRecord
{
    protected static string $resource = StubProfileResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['is_stub']     = true;
        $data['is_deceased'] = $data['is_deceased'] ?? true;
        $data['email']       = null;
        $data['password']    = null;

        return $data;
    }

    protected function afterCreate(): void
    {
        $record  = $this->getRecord();
        $profile = $record->profile;

        if ($profile && empty($profile->full_name)) {
            $profile->update(['full_name' => $record->name]);
        }
    }
}
