<?php

namespace App\Filament\Resources\EventResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class FinancialContributionsRelationManager extends RelationManager
{
    protected static string $relationship = 'financialContributions';

    protected static ?string $title = 'Financial Contributions (Patungan)';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('contributor_id')
                    ->relationship('contributor', 'name')
                    ->required()
                    ->searchable(),
                Forms\Components\TextInput::make('amount')
                    ->numeric()
                    ->required()
                    ->prefix('IDR'),
                Forms\Components\Select::make('currency')
                    ->options([
                        'IDR' => 'Indonesian Rupiah (IDR)',
                        'USD' => 'US Dollar (USD)',
                        'MYR' => 'Malaysian Ringgit (MYR)',
                    ])
                    ->default('IDR')
                    ->required(),
                Forms\Components\Select::make('payment_method')
                    ->options([
                        'transfer'    => 'Bank Transfer',
                        'cash'        => 'Cash',
                        'gopay'       => 'GoPay',
                        'ovo'         => 'OVO',
                        'dana'        => 'DANA',
                        'bca'         => 'BCA Mobile',
                        'other'       => 'Other',
                    ])
                    ->required(),
                Forms\Components\TextInput::make('reference_number')
                    ->maxLength(255),
                Forms\Components\Textarea::make('note')
                    ->maxLength(500)
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('contributor.name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('amount')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('currency'),
                Tables\Columns\TextColumn::make('payment_method')
                    ->badge(),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'confirmed',
                        'danger'  => 'rejected',
                    ]),
                Tables\Columns\TextColumn::make('reference_number')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('confirmedBy.name')
                    ->label('Confirmed By')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('confirmed_at')
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending'   => 'Pending',
                        'confirmed' => 'Confirmed',
                        'rejected'  => 'Rejected',
                    ]),
                Tables\Filters\SelectFilter::make('currency')
                    ->options([
                        'IDR' => 'IDR',
                        'USD' => 'USD',
                        'MYR' => 'MYR',
                    ]),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\Action::make('confirm')
                    ->label('Verify')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->visible(fn ($record): bool => $record->status === 'pending')
                    ->action(function ($record) {
                        $record->update([
                            'status'       => 'confirmed',
                            'confirmed_by' => auth()->id(),
                            'confirmed_at' => now(),
                        ]);
                    })
                    ->requiresConfirmation(),
                Tables\Actions\Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn ($record): bool => $record->status === 'pending')
                    ->action(fn ($record) => $record->update(['status' => 'rejected']))
                    ->requiresConfirmation(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public function canCreate(): bool
    {
        $event = $this->getOwnerRecord();
        $user  = auth()->user();

        return $user?->isSuperAdmin()
            || $event->creator_id === $user?->id
            || $user?->eventCommittees()->where('event_id', $event->id)->whereIn('role', ['coordinator', 'treasurer'])->exists();
    }
}
