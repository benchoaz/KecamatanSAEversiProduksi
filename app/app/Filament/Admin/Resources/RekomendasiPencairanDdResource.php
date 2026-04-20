<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\RekomendasiPencairanDdResource\Pages;
use App\Models\RekomendasiPencairanDd;
use App\Services\EvaluasiPencairanService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Notifications\Notification;

class RekomendasiPencairanDdResource extends Resource
{
    protected static ?string $model = RekomendasiPencairanDd::class;

    protected static ?string $navigationIcon = 'heroicon-o-shield-check';
    protected static ?string $navigationGroup = 'EKONOMI & PEMBANGUNAN';
    protected static ?string $navigationLabel = 'Validasi Rekomendasi DD';
    protected static ?int $navigationSort = -4;

    public static function canViewAny(): bool
    {
        return auth()->check();
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        
        $user = auth()->user();
        if ($user->desa_id) {
            $query->where('desa_id', $user->desa_id);
        }
        
        return $query;
    }

    public static function form(Form $form): Form
    {
        $user = auth()->user();
        $isDesa = $user->desa_id !== null;

        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Pengajuan')
                    ->schema([
                        Forms\Components\Select::make('desa_id')
                            ->label('Desa')
                            ->relationship('desa', 'nama_desa')
                            ->required()
                            ->default($user->desa_id)
                            ->disabled($isDesa)
                            ->dehydrated(),

                        Forms\Components\Select::make('tahap_pencairan')
                            ->label('Tahap Pengajuan (PMK 7/2026)')
                            ->options([
                                1 => 'Tahap I (40%)',
                                2 => 'Tahap II (40%)',
                                3 => 'Tahap III (20%)',
                            ])
                            ->required()
                            ->disabled(fn ($record) => $record !== null && $isDesa), // Desa cannot edit once created
                    ])->columns(2),

                Forms\Components\Section::make('Dokumen Prasyarat (Upload Desa)')
                    ->description('Silakan periksa dokumen berikut sebelum melakukan evaluasi kelayakan.')
                    ->schema([
                        Forms\Components\Placeholder::make('view_documents')
                            ->label('')
                            ->content(fn ($record) => $record ? view('filament.forms.components.view-pencairan-docs', [
                                'docs' => \App\Models\DokumenPencairanDesa::where('desa_id', $record->desa_id)->get()
                            ]) : 'Belum ada dokumen yang diunggah.'),
                    ])
                    ->visible(!$isDesa && fn ($record) => $record !== null),

                Forms\Components\Section::make('Hasil Validasi Kecamatan')
                    ->schema([
                        Forms\Components\Select::make('status_akhir')
                            ->label('Status (Otomatis / Verifikator)')
                            ->options([
                                'BELUM ADA' => 'Menunggu Berkas',
                                'TUNDA' => 'Tunda (Cek LPJ/APBDes)',
                                'TIDAK LAYAK' => 'Tidak Layak (Kurang Dokumen)',
                                'PERBAIKAN' => 'Perbaikan (Menunggu Revisi)',
                                'VALID' => 'Valid (Reviewing)',
                                'LAYAK CAIR' => 'Layak Cair (Siap Generate)',
                            ])
                            ->default('BELUM ADA')
                            ->required()
                            ->disabled($isDesa),

                        Forms\Components\Textarea::make('catatan_revisi')
                            ->label('Catatan dari Verifikator')
                            ->maxLength(65535)
                            ->columnSpanFull()
                            ->disabled($isDesa),

                        Forms\Components\FileUpload::make('pdf_rekomendasi_camat')
                            ->label('Surat Rekomendasi Camat (PDF)')
                            ->directory('rekomendasi-camat')
                            ->acceptedFileTypes(['application/pdf'])
                            ->disabled($isDesa)
                            ->columnSpanFull(),
                    ])->visible(fn ($record) => $record !== null), // Only show on edit
            ]);
    }

    public static function table(Table $table): Table
    {
        $user = auth()->user();
        $isDesa = $user->desa_id !== null;

        return $table
            ->columns([
                Tables\Columns\TextColumn::make('desa.nama_desa')
                    ->label('Desa')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('tahap_pencairan')
                    ->label('Tahap')
                    ->badge()
                    ->formatStateUsing(fn ($state) => 'Tahap ' . $state),
                Tables\Columns\TextColumn::make('status_akhir')
                    ->label('Status Validasi')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'BELUM ADA' => 'gray',
                        'TUNDA', 'TIDAK LAYAK' => 'danger',
                        'PERBAIKAN' => 'warning',
                        'VALID' => 'info',
                        'LAYAK CAIR' => 'success',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Tgl Update')
                    ->dateTime('d M Y')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('desa_id')
                    ->relationship('desa', 'nama_desa')
                    ->label('Filter Desa')
                    ->visible(!$isDesa),
            ])
            ->actions([
                Tables\Actions\Action::make('cek_kelayakan')
                    ->label('Evaluasi Kelayakan')
                    ->icon('heroicon-o-magnifying-glass-circle')
                    ->color('info')
                    ->visible(!$isDesa)
                    ->action(function (RekomendasiPencairanDd $record) {
                        $service = new EvaluasiPencairanService();
                        $hasil = $service->evaluasiKesiapanPencairan($record->desa_id, $record->tahap_pencairan);
                        
                        $record->status_akhir = $hasil['status'];
                        $record->catatan_revisi = $hasil['pesan'];
                        $record->save();

                        Notification::make()
                            ->title('Evaluasi Selesai')
                            ->body('Status berubah menjadi: ' . $hasil['status'])
                            ->success()
                            ->send();
                    }),
                Tables\Actions\Action::make('download_rekomendasi')
                    ->label('Unduh Rekomendasi')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('success')
                    ->url(fn (RekomendasiPencairanDd $record): string => asset('storage/' . $record->pdf_rekomendasi_camat))
                    ->openUrlInNewTab()
                    ->visible(fn (RekomendasiPencairanDd $record) => $record->pdf_rekomendasi_camat !== null),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()->visible(!$isDesa),
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
            'index' => Pages\ListRekomendasiPencairanDds::route('/'),
            'create' => Pages\CreateRekomendasiPencairanDd::route('/create'),
            'edit' => Pages\EditRekomendasiPencairanDd::route('/{record}/edit'),
        ];
    }
}
