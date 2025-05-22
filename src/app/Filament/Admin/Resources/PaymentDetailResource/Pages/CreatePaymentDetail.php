<?php

namespace App\Filament\Admin\Resources\PaymentDetailResource\Pages;

use App\Filament\Admin\Resources\PaymentDetailResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreatePaymentDetail extends CreateRecord
{
    protected static string $resource = PaymentDetailResource::class;
}
