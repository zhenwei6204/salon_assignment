<?php

namespace App\Filament\Resources\Stylists\Pages;

use App\Filament\Resources\Stylists\StylistResource;
use Filament\Resources\Pages\CreateRecord;
use App\Factories\StylistFactory;
use Illuminate\Database\Eloquent\Model;


class CreateStylist extends CreateRecord
{
    protected static string $resource = StylistResource::class;

    protected function handleRecordCreation(array $data): Model
{
    return \App\Factories\StylistFactory::create($data);
}
}
