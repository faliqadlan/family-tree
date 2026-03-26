<?php

namespace App\Filament\Resources;

use App\Enums\GenderOptions;
use App\Filament\Resources\StubProfileResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class StubProfileResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-minus';

    protected static ?string $navigationGroup = 'Family Tree';

    protected static ?string $navigationLabel = 'Stub Profiles';

    protected static ?string $modelLabel = 'Stub Profile';

    protected static ?string $pluralModelLabel = 'Stub Profiles';

    protected static ?string $slug = 'stub-profiles';

    protected static ?int $navigationSort = 3;

    public static function canAccess(): bool
    {
        return auth()->user()?->isSuperAdmin() ?? false;
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('is_stub', true);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Stub Profile Identity')
                    ->description('Create a profile for a deceased or missing ancestor. No login credentials are required.')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Full Name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Toggle::make('is_deceased')
                            ->label('Mark as Deceased')
                            ->default(true)
                            ->helperText('Indicates this ancestor has passed away.'),
                        Forms\Components\Hidden::make('is_stub')
                            ->default(true),
                        Forms\Components\Hidden::make('role')
                            ->default('user'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Personal Details')
                    ->relationship('profile')
                    ->schema([
                        Forms\Components\TextInput::make('full_name')
                            ->label('Full Name (Profile)')
                            ->maxLength(255)
                            ->helperText('Leave blank to use the name above.'),
                        Forms\Components\TextInput::make('nickname')
                            ->maxLength(255),
                        Forms\Components\Select::make('gender')
                            ->options(GenderOptions::OPTIONS),
                        Forms\Components\DatePicker::make('date_of_birth')
                            ->label('Date of Birth'),
                        Forms\Components\DatePicker::make('date_of_death')
                            ->label('Date of Death'),
                        Forms\Components\TextInput::make('place_of_birth')
                            ->maxLength(255),
                        Forms\Components\Textarea::make('bio')
                            ->maxLength(1000)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Family Links')
                    ->relationship('profile')
                    ->schema([
                        Forms\Components\TextInput::make('father_name')
                            ->maxLength(255)
                            ->helperText('Enter the full name of the father to auto-link in the graph.'),
                        Forms\Components\TextInput::make('mother_name')
                            ->maxLength(255)
                            ->helperText('Enter the full name of the mother to auto-link in the graph.'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_deceased')
                    ->label('Deceased')
                    ->boolean()
                    ->trueColor('danger')
                    ->falseColor('gray'),
                Tables\Columns\TextColumn::make('profile.gender')
                    ->label('Gender')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'male'   => 'info',
                        'female' => 'pink',
                        default  => 'gray',
                    }),
                Tables\Columns\TextColumn::make('profile.date_of_birth')
                    ->label('Birth')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('profile.date_of_death')
                    ->label('Death')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('profile.father_name')
                    ->label('Father')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('profile.mother_name')
                    ->label('Mother')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('profile.graph_node_id')
                    ->label('Graph Node')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_deceased')
                    ->label('Deceased'),
                Tables\Filters\SelectFilter::make('gender')
                    ->relationship('profile', 'gender')
                    ->options(GenderOptions::OPTIONS),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            'index'  => Pages\ListStubProfiles::route('/'),
            'create' => Pages\CreateStubProfile::route('/create'),
            'edit'   => Pages\EditStubProfile::route('/{record}/edit'),
        ];
    }
}
