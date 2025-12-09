<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ItemResource\Pages;
use App\Filament\Resources\ItemResource\RelationManagers;
use App\Models\Item;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ItemResource extends Resource
{
    protected static ?string $modelLabel = 'Barang';
    protected static ?string $pluralModelLabel = 'Barang';
    protected static ?string $navigationLabel = 'Barang';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('category_id')
                    ->label('Kategori')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload()
                    ->createOptionForm([
                        Forms\Components\TextInput::make('name')
                            ->label('Nama Kategori')
                            ->required(),
                        Forms\Components\Textarea::make('description')
                            ->label('Deskripsi'),
                    ]),
                Forms\Components\TextInput::make('name')
                    ->label('Nama Barang')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('sku')
                    ->label('SKU (Kode Barang)')
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),
                Forms\Components\TextInput::make('minimum_stock')
                    ->label('Stok Minimum')
                    ->numeric()
                    ->required()
                    ->default(0),
                Forms\Components\TextInput::make('current_stock')
                    ->label('Stok Saat Ini')
                    ->numeric()
                    ->required()
                    ->default(0)
                    ->disabledOn('edit'),
                Forms\Components\KeyValue::make('attributes')
                    ->label('Atribut Tambahan')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Barang')
                    ->searchable(),
                Tables\Columns\TextColumn::make('sku')
                    ->label('SKU')
                    ->searchable(),
                Tables\Columns\TextColumn::make('current_stock')
                    ->label('Stok')
                    ->numeric()
                    ->sortable()
                    ->color(fn (Item $record) => $record->current_stock <= $record->minimum_stock ? 'danger' : null),
                Tables\Columns\TextColumn::make('minimum_stock')
                    ->label('Min. Stok')
                    ->numeric()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\Filter::make('low_stock')
                    ->label('Stok Menipis')
                    ->query(fn (Builder $query): Builder => $query->whereColumn('current_stock', '<=', 'minimum_stock')),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('add_stock')
                    ->label('Tambah Stok')
                    ->icon('heroicon-o-plus')
                    ->form([
                        Forms\Components\TextInput::make('quantity')
                            ->label('Jumlah Penambahan')
                            ->numeric()
                            ->required()
                            ->minValue(1),
                        Forms\Components\Textarea::make('note')
                            ->label('Catatan'),
                    ])
                    ->action(function (Item $record, array $data) {
                        $record->transactions()->create([
                            'type' => 'in',
                            'quantity' => $data['quantity'],
                            'note' => $data['note'],
                        ]);
                        $record->increment('current_stock', $data['quantity']);
                        \Filament\Notifications\Notification::make()
                            ->title('Stok berhasil ditambahkan')
                            ->success()
                            ->send();
                    }),
                Tables\Actions\Action::make('sell_stock')
                    ->label('Kurangi Stok')
                    ->icon('heroicon-o-minus')
                    ->color('danger')
                    ->form([
                        Forms\Components\TextInput::make('quantity')
                            ->label('Jumlah Pengurangan')
                            ->numeric()
                            ->required()
                            ->minValue(1)
                            ->maxValue(fn (Item $record) => $record->current_stock),
                        Forms\Components\Textarea::make('note')
                            ->label('Catatan'),
                    ])
                    ->action(function (Item $record, array $data) {
                        $record->transactions()->create([
                            'type' => 'out',
                            'quantity' => $data['quantity'],
                            'note' => $data['note'],
                        ]);
                        $record->decrement('current_stock', $data['quantity']);
                        \Filament\Notifications\Notification::make()
                            ->title('Stok berhasil dikurangi')
                            ->success()
                            ->send();
                    }),
            ])
            ->headerActions([
                Tables\Actions\Action::make('export_csv')
                    ->label('Download CSV')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(function ($livewire) {
                        $items = $livewire->getFilteredTableQuery()->get();
                        $csvData = [];
                        $csvData[] = ['Nama Barang', 'SKU', 'Stok Saat Ini', 'Stok Minimum', 'Terakhir Diupdate'];

                        foreach ($items as $item) {
                            $csvData[] = [
                                $item->name,
                                $item->sku,
                                $item->current_stock,
                                $item->minimum_stock,
                                $item->updated_at,
                            ];
                        }

                        $output = fopen('php://output', 'w');
                        ob_start();
                        foreach ($csvData as $row) {
                            fputcsv($output, $row);
                        }
                        $csv = ob_get_clean();
                        fclose($output);

                        return response()->streamDownload(function () use ($csv) {
                            echo $csv;
                        }, 'items-export-' . date('Y-m-d') . '.csv');
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\TransactionsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListItems::route('/'),
            'create' => Pages\CreateItem::route('/create'),
            'edit' => Pages\EditItem::route('/{record}/edit'),
        ];
    }
}
