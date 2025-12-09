<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Form;
use Filament\Forms;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use App\Models\Item;
use Illuminate\Support\Facades\DB;

class BulkStockAdjustment extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    
    protected static ?string $navigationLabel = 'Transaksi Massal';
    
    protected static ?string $title = 'Transaksi Stok Massal';

    protected static string $view = 'filament.pages.bulk-stock-adjustment';
    
    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Repeater::make('transactions')
                    ->label('Daftar Transaksi')
                    ->schema([
                        Forms\Components\Select::make('item_id')
                            ->label('Barang')
                            ->options(Item::query()->pluck('name', 'id'))
                            ->searchable()
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(fn ($state, Forms\Set $set) => $set('current_stock', Item::find($state)?->current_stock ?? 0)),
                        Forms\Components\TextInput::make('current_stock')
                            ->label('Stok Saat Ini')
                            ->disabled()
                            ->dehydrated(false),
                        Forms\Components\Select::make('type')
                            ->label('Tipe')
                            ->options([
                                'in' => 'Masuk (Tambah)',
                                'out' => 'Keluar (Kurang)',
                            ])
                            ->required()
                            ->default('in'),
                        Forms\Components\TextInput::make('quantity')
                            ->label('Jumlah')
                            ->numeric()
                            ->minValue(1)
                            ->required(),
                        Forms\Components\TextInput::make('note')
                            ->label('Catatan'),
                    ])
                    ->columns(5)
                    ->defaultItems(1)
                    ->addActionLabel('Tambah Transaksi Baru'),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        DB::transaction(function () use ($data) {
            foreach ($data['transactions'] as $transaction) {
                $item = Item::find($transaction['item_id']);
                
                if ($transaction['type'] === 'out' && $item->current_stock < $transaction['quantity']) {
                    Notification::make()
                        ->title("Stok tidak cukup untuk items: {$item->name}")
                        ->danger()
                        ->send();
                    throw new \Exception("Insufficient stock");
                }

                $item->transactions()->create([
                    'type' => $transaction['type'],
                    'quantity' => $transaction['quantity'],
                    'note' => $transaction['note'] ?? null,
                ]);

                if ($transaction['type'] === 'in') {
                    $item->increment('current_stock', $transaction['quantity']);
                } else {
                    $item->decrement('current_stock', $transaction['quantity']);
                }
            }
        });

        Notification::make()
            ->title('Transaksi massal berhasil disimpan')
            ->success()
            ->send();
            
        $this->form->fill();
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Simpan Transaksi')
                ->submit('save'),
        ];
    }
}
