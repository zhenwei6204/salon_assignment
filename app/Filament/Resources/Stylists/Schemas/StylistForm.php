<?php

// app/Filament/Resources/Stylists/Schemas/StylistForm.php

namespace App\Filament\Resources\Stylists\Schemas;

use Filament\Schemas;  // Correct import for Filament\Schemas
use Filament\Forms\Components\TextInput;  // Import form components from Filament\Forms
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\MultiSelect;  // Import MultiSelect for relationship
use Illuminate\Validation\Rule;

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
                ->label('Title')
                ->disabled()
                ->maxLength(255),
            TextInput::make('experience_years')
                ->label('Experience Years')
                ->numeric()
                ->default(0)
                ->reactive() // makes it live-reactive
                ->afterStateUpdated(function ($state, callable $set) {
                    if ($state >= 10) {
                        $set('title', 'Senior Stylist');
                    } elseif ($state >= 5) {
                        $set('title', 'Intermediate Stylist');
                    } else {
                        $set('title', 'Junior Stylist');
                    }
                }),
            // Replace boolean field with Toggle component
            Toggle::make('is_active')
                ->label('Active'),
            TextInput::make('email')
                ->required()
                ->email()
                ->label('Email address')
                ->rules(function ($get, $record) {
                    $userId = $record?->user_id; // get the linked user_id if editing
            
                    return [
                        // Ignore the linked user record in the users table
                        Rule::unique('users', 'email')->ignore($userId),
            
                        // Ignore the stylist record in the stylists table
                        Rule::unique('stylists', 'email')->ignore($record?->id),
                    ];
                }),     
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

