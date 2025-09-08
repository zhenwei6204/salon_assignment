<?php

namespace App\Filament\Resources\Stylists;

use App\Filament\Resources\Stylists\Pages\CreateStylist;
use App\Filament\Resources\Stylists\Pages\EditStylist;
use App\Filament\Resources\Stylists\Pages\ListStylists;
use App\Filament\Resources\Stylists\Schemas\StylistForm;
use App\Filament\Resources\Stylists\Tables\StylistsTable;
use App\Models\Stylist;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema; // Use the correct Schema import
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class StylistResource extends Resource
{
    protected static ?string $model = Stylist::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;
    
    protected static ?string $navigationLabel = 'Stylists';
    
    protected static ?string $modelLabel = 'Stylist';
    
    protected static ?string $pluralModelLabel = 'Stylists';
    
    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return StylistForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return StylistsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            // Add relationships here if needed
            // 'services' => ServicesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListStylists::route('/'),
            'create' => CreateStylist::route('/create'),
            'edit' => EditStylist::route('/{record}/edit'),
        ];
    }
    
    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }
    
    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'title', 'specializations', 'email'];
    }
}