<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProfileResource\Pages;
use App\Models\Profile;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ProfileResource extends Resource
{
    protected static ?string $model = Profile::class;

    protected static ?string $navigationIcon = 'heroicon-o-identification';

    protected static ?string $navigationGroup = 'Administration';

    protected static ?int $navigationSort = 2;

    public static function canAccess(): bool
    {
        return auth()->user()?->isSuperAdmin() ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Personal Information')
                    ->schema([
                        Forms\Components\TextInput::make('full_name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('nickname')
                            ->maxLength(255),
                        Forms\Components\Select::make('gender')
                            ->options([
                                'male'   => 'Male',
                                'female' => 'Female',
                            ]),
                        Forms\Components\DatePicker::make('date_of_birth'),
                        Forms\Components\DatePicker::make('date_of_death'),
                        Forms\Components\TextInput::make('place_of_birth')
                            ->maxLength(255),
                        Forms\Components\Textarea::make('bio')
                            ->maxLength(1000)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Contact & Privacy')
                    ->schema([
                        Forms\Components\TextInput::make('phone')
                            ->tel()
                            ->maxLength(30),
                        Forms\Components\Select::make('phone_privacy')
                            ->options([
                                'public'  => 'Public',
                                'masked'  => 'Masked (requires request)',
                                'private' => 'Private',
                            ])
                            ->default('private'),
                        Forms\Components\Select::make('email_privacy')
                            ->options([
                                'public'  => 'Public',
                                'masked'  => 'Masked (requires request)',
                                'private' => 'Private',
                            ])
                            ->default('private'),
                        Forms\Components\Select::make('dob_privacy')
                            ->options([
                                'public'  => 'Public',
                                'masked'  => 'Masked (requires request)',
                                'private' => 'Private',
                            ])
                            ->default('public'),
                        Forms\Components\Textarea::make('address')
                            ->maxLength(500)
                            ->columnSpanFull(),
                        Forms\Components\Select::make('address_privacy')
                            ->options([
                                'public'  => 'Public',
                                'masked'  => 'Masked (requires request)',
                                'private' => 'Private',
                            ])
                            ->default('private'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Family Links')
                    ->schema([
                        Forms\Components\TextInput::make('father_name')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('mother_name')
                            ->maxLength(255),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('full_name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.email')
                    ->label('User Email')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('gender')
                    ->badge()
                    ->color(fn (string $value): string => match ($value) {
                        'male'   => 'info',
                        'female' => 'pink',
                        default  => 'gray',
                    }),
                Tables\Columns\TextColumn::make('date_of_birth')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('phone_privacy')
                    ->badge()
                    ->color(fn (string $value): string => match ($value) {
                        'public'  => 'success',
                        'masked'  => 'warning',
                        'private' => 'danger',
                        default   => 'gray',
                    })
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('gender')
                    ->options([
                        'male'   => 'Male',
                        'female' => 'Female',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListProfiles::route('/'),
            'edit'   => Pages\EditProfile::route('/{record}/edit'),
        ];
    }
}
