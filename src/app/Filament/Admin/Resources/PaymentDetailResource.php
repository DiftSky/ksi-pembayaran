<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\PaymentDetailResource\Pages;
use App\Models\PaymentDetail;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PaymentDetailResource extends Resource
{
    protected static ?string $model = PaymentDetail::class;
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationGroup = 'Transactions';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('payment_id')
                    ->relationship('payment', 'reference_no')
                    ->required()
                    ->searchable()
                    ->preload(),
                Forms\Components\Select::make('method_id')
                    ->relationship('paymentMethod', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),
                Forms\Components\TextInput::make('account_number')
                    ->maxLength(255),
                Forms\Components\TextInput::make('bank_name')
                    ->maxLength(255),
                Forms\Components\TextInput::make('card_type')
                    ->maxLength(255),
                Forms\Components\TextInput::make('last_four_digits')
                    ->maxLength(4),
                Forms\Components\DatePicker::make('expiry_date'),
                Forms\Components\TextInput::make('holder_name')
                    ->maxLength(255),
                Forms\Components\Textarea::make('notes')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('payment.reference_no')
                    ->searchable(),
                Tables\Columns\TextColumn::make('paymentMethod.name')
                    ->sortable(),
                Tables\Columns\TextColumn::make('account_number')
                    ->searchable(),
                Tables\Columns\TextColumn::make('bank_name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('card_type')
                    ->searchable(),
                Tables\Columns\TextColumn::make('last_four_digits'),
                Tables\Columns\TextColumn::make('holder_name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPaymentDetails::route('/'),
            'create' => Pages\CreatePaymentDetail::route('/create'),
            'edit' => Pages\EditPaymentDetail::route('/{record}/edit'),
        ];
    }
}
