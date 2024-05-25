<?php

namespace App\Filament\Resources\UserResource\RelationManagers;

use App\Models\wishlist;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class WishlistRelationManager extends RelationManager
{
    protected static string $relationship = 'wishlist';

    public function form(Form $form): Form
    {
        return $form
            ->schema([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('user_id')
            ->columns([
                Tables\Columns\TextColumn::make('user.fullname'),
                Tables\Columns\TextColumn::make('products.name')
                    ->listWithLineBreaks(),
                Tables\Columns\TextColumn::make('products')
                    ->label('Quantity')
                    ->getStateUsing(function (wishlist $record) {
                        return $record->products->map(function ($product) {
                            return "{$product->pivot->quantity}";
                        })->toArray();
                    })
                    ->listWithLineBreaks(),
            ])
            ->filters([
                //
            ])
            ->headerActions([])
            ->actions([])
            ->bulkActions([]);
    }
}
