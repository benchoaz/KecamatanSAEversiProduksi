<div class="space-y-2">
    @if($docs->count() > 0)
        <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                <tr>
                    <th scope="col" class="px-4 py-2">Nama Dokumen</th>
                    <th scope="col" class="px-4 py-2">Tahun</th>
                    <th scope="col" class="px-4 py-2">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($docs as $doc)
                    <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                        <td class="px-4 py-2 font-medium text-gray-900 dark:text-white">
                            {{ strtoupper(str_replace('_', ' ', $doc->kategori_dokumen)) }}
                        </td>
                        <td class="px-4 py-2 italic">
                            {{ $doc->tahun }}
                        </td>
                        <td class="px-4 py-2">
                            <a href="{{ asset('storage/' . $doc->file_path) }}" 
                               target="_blank"
                               class="inline-flex items-center px-3 py-1 text-xs font-medium text-center text-white bg-blue-600 rounded-lg hover:bg-blue-700 focus:ring-4 focus:outline-none focus:ring-blue-300 dark:bg-blue-500 dark:hover:bg-blue-600 dark:focus:ring-blue-800">
                                <svg class="w-3 h-3 mr-1" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 11V6m0 8h.01M19 10a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                                </svg>
                                Lihat PDF
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p class="text-sm text-red-500 italic">Belum ada dokumen prasyarat yang diunggah oleh desa untuk tahun berjalan.</p>
    @endif
</div>
