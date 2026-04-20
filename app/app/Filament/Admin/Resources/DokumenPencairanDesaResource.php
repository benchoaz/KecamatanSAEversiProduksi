<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\DokumenPencairanDesaResource\Pages;
use App\Models\DokumenPencairanDesa;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class DokumenPencairanDesaResource extends Resource
{
    protected static ?string $model = DokumenPencairanDesa::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-plus';
    protected static ?string $navigationGroup = 'EKONOMI & PEMBANGUNAN';
    protected static ?string $navigationLabel = 'Syarat Pencairan DD';
    protected static ?int $navigationSort = -5;

    public static function canViewAny(): bool
    {
        return auth()->check(); // Allowing all authenticated users for now, and filtering logic handles individual access
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        
        // Scope for Operator Desa
        $user = auth()->user();
        if ($user->desa_id) {
            $query->where('desa_id', $user->desa_id);
        }
        
        return $query;
    }

    public static function form(Form $form): Form
    {
        $user = auth()->user();
        return $form
            ->schema([
                Forms\Components\Select::make('desa_id')
                    ->label('Desa')
                    ->relationship('desa', 'nama_desa')
                    ->required()
                    ->default($user->desa_id)
                    ->disabled(fn () => $user->desa_id !== null)
                    ->dehydrated(),

                Forms\Components\TextInput::make('tahun')
                    ->label('Tahun Anggaran')
                    ->numeric()
                    ->default(date('Y'))
                    ->required(),

                Forms\Components\Select::make('kategori_dokumen')
                    ->label('Kategori Dokumen Syarat (PMK 7/2026)')
                    ->options([
                        'apbdes_2026' => 'Perdes APBDes 2026',
                        'perkades_penjabaran' => 'Perkades Penjabaran APBDes',
                        'lpj_2025' => 'Laporan Pertanggungjawaban (LPJ) 2025 100%',
                        'laporan_realisasi_tahap_sebelumnya' => 'Laporan Realisasi Tahap Sebelumnya',
                    ])
                    ->required(),

                Forms\Components\FileUpload::make('file_path')
                    ->label('Unggah File (PDF)')
                    ->directory('dokumen-pencairan')
                    ->acceptedFileTypes(['application/pdf'])
                    ->maxSize(5120) // 5 MB
                    ->required()
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('desa.nama_desa')
                    ->label('Desa')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('kategori_dokumen')
                    ->label('Kategori')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'apbdes_2026' => 'primary',
                        'perkades_penjabaran' => 'warning',
                        'lpj_2025' => 'success',
                        'laporan_realisasi_tahap_sebelumnya' => 'info',
                    })
                    ->formatStateUsing(fn (string $state): string => strtoupper(str_replace('_', ' ', $state))),
                Tables\Columns\TextColumn::make('tahun')
                    ->label('Tahun')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tgl Upload')
                    ->dateTime('d M Y H:i'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('desa_id')
                    ->relationship('desa', 'nama_desa')
                    ->label('Filter Desa')
                    ->visible(fn () => auth()->user()->desa_id === null),
                Tables\Filters\SelectFilter::make('kategori_dokumen')
                    ->options([
                        'apbdes_2026' => 'APBDes 2026',
                        'perkades_penjabaran' => 'Perkades Penjabaran',
                        'lpj_2025' => 'LPJ 2025',
                        'laporan_realisasi_tahap_sebelumnya' => 'Realisasi Tahap Lalu',
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('download')
                    ->label('Download PDF')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->url(fn (DokumenPencairanDesa $record): string => asset('storage/' . $record->file_path))
                    ->openUrlInNewTab(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDokumenPencairanDesas::route('/'),
            'create' => Pages\CreateDokumenPencairanDesa::route('/create'),
            'edit' => Pages\EditDokumenPencairanDesa::route('/{record}/edit'),
        ];
    }
}
