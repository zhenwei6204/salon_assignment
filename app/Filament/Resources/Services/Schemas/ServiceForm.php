<?php

namespace App\Filament\Resources\Services\Schemas;

use Filament\Schemas\Schema;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\FileUpload;
class ServiceForm
{
public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // Add category selection (dropdown)
                Select::make('category_id')
                    ->label('Category')
                    ->options(\App\Models\Category::all()->pluck('name', 'id'))  // List categories
                    ->required(),
                // Service name input
                TextInput::make('name')
                    ->label('Service Name')
                    ->required(),
                // Service description input
                Textarea::make('description')
                    ->label('Description'),
                // Service benefits input
                Textarea::make('benefits')
                    ->label('Benefits'),
                // Service price input
                TextInput::make('price')
                    ->label('Price')
                    ->numeric()
                    ->required(),
                // Service duration input (in minutes)
                TextInput::make('duration')
                    ->label('Duration (minutes)')
                    ->numeric()
                    ->required(),
                // Toggle to make the service available or not
                Toggle::make('is_available')
                    ->label('Is Available')
                    ->default(true),
                // Stylist qualifications input
                Textarea::make('stylist_qualifications')
                    ->label('Stylist Qualifications'),
                // Image URL input
               FileUpload::make('image_url')
                ->image()
                ->preserveFilenames()       
    
            ]);
    }
}

