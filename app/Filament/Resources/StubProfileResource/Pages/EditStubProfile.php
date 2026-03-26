<?php

namespace App\Filament\Resources\StubProfileResource\Pages;

use App\Filament\Resources\StubProfileResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditStubProfile extends EditRecord
{
    protected static string $resource = StubProfileResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
