<?php

// app/Filament/Resources/Stylists/Schemas/StylistForm.php

namespace App\Filament\Resources\Stylists\Schemas;

use Filament\Schemas;  // Correct import for Filament\Schemas
use Filament\Forms\Components\TextInput;  // Import form components from Filament\Forms
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\MultiSelect;  // Import MultiSelect for relationship

use App\Models\Service;  // Import Service model for options in MultiSelect

class StylistForm
{
    public static function configure(Schemas\Schema $schema): Schemas\Schema
    {
        return $schema->schema([
            TextInput::make('name')
                ->required()
                ->maxLength(255),
            TextInput::make('title')
                ->maxLength(255),
            Textarea::make('specializations')
                ->nullable()
                ->columnSpanFull(),
            TextInput::make('experience_years')
                ->numeric()
                ->default(0),
            TextInput::make('rating')
                ->numeric()
                ->default(0.0),
            TextInput::make('review_count')
                ->numeric()
                ->default(0),
            // Replace boolean field with Toggle component
            Toggle::make('is_active')
                ->label('Active'),
            TextInput::make('phone')
                ->nullable()
                ->tel(),
            TextInput::make('email')
                ->nullable()
                ->email()
                ->label('Email address'),
            Textarea::make('bio')
                ->nullable()
                ->columnSpanFull(),
            FileUpload::make('image_url')
                ->image()
                ->preserveFilenames(),       

            // Add MultiSelect for services relationship
            MultiSelect::make('services')  // Using MultiSelect for Many-to-Many relation
                ->label('Assigned Services') 
                ->relationship('services', 'name')  // 'services' is the relationship method, 'name' is the column to display
                ->options(Service::all()->pluck('name', 'id')->toArray())  // Fetch all services for selection

                ->afterStateUpdated(function ($state) {
                     return array_unique($state);
                      }),
        ]);
    }
}

