{{-- 
    Composant réutilisable : Boutons Document (Télécharger, Imprimer, Envoyer)
    Usage : @include('components.document-actions', ['type' => 'facture', 'document' => $invoice])
    
    Modification 2 : Boutons dans les documents
--}}

@props(['type', 'document', 'showExpert' => false, 'showFournisseur' => false])

<div x-data="{ showEmailModal: false }" class="flex flex-wrap items-center gap-2">
    {{-- Télécharger PDF --}}
    <a href="{{ route('documents.download', [$type, $document->id]) }}" 
       class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium rounded-lg border border-gray-300 text-gray-700 bg-white hover:bg-gray-50 transition">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
        </svg>
        Télécharger PDF
    </a>

    {{-- Imprimer --}}
    <a href="{{ route('documents.print', [$type, $document->id]) }}" target="_blank"
       class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium rounded-lg border border-gray-300 text-gray-700 bg-white hover:bg-gray-50 transition">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
        </svg>
        Imprimer
    </a>

    {{-- Envoyer par Email --}}
    <button @click="showEmailModal = true"
            class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium rounded-lg border border-blue-300 text-blue-700 bg-blue-50 hover:bg-blue-100 transition">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
        </svg>
        Envoyer par Email
    </button>

    {{-- Modal d'envoi email --}}
    <div x-show="showEmailModal" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
         @keydown.escape.window="showEmailModal = false">
        <div @click.outside="showEmailModal = false"
             class="bg-white rounded-xl shadow-2xl w-full max-w-md mx-4 overflow-hidden">
            <div class="px-6 py-4 bg-gradient-to-r from-blue-600 to-blue-700 text-white">
                <h3 class="text-lg font-semibold">Envoyer par Email</h3>
                <p class="text-blue-100 text-sm mt-1">{{ ucfirst($type) }} {{ $document->numero ?? '' }}</p>
            </div>

            <form action="{{ route('documents.email', [$type, $document->id]) }}" method="POST" class="p-6">
                @csrf
                <div x-data="{ recipientType: 'client' }" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Destinataire</label>
                        <div class="space-y-2">
                            {{-- Client --}}
                            @if($document->client ?? false)
                            <label class="flex items-center gap-3 p-3 rounded-lg border cursor-pointer hover:bg-gray-50 transition"
                                   :class="recipientType === 'client' ? 'border-blue-500 bg-blue-50' : 'border-gray-200'">
                                <input type="radio" name="recipient_type" value="client" x-model="recipientType" class="text-blue-600">
                                <div>
                                    <span class="text-sm font-medium text-gray-900">Client</span>
                                    <span class="block text-xs text-gray-500">{{ $document->client->email ?? 'Pas d\'email' }}</span>
                                </div>
                            </label>
                            @endif

                            {{-- Expert --}}
                            @if($showExpert)
                            <label class="flex items-center gap-3 p-3 rounded-lg border cursor-pointer hover:bg-gray-50 transition"
                                   :class="recipientType === 'expert' ? 'border-blue-500 bg-blue-50' : 'border-gray-200'">
                                <input type="radio" name="recipient_type" value="expert" x-model="recipientType" class="text-blue-600">
                                <div>
                                    <span class="text-sm font-medium text-gray-900">Expert</span>
                                    <span class="block text-xs text-gray-500">Expert associé au dossier</span>
                                </div>
                            </label>
                            @endif

                            {{-- Fournisseur --}}
                            @if($showFournisseur)
                            <label class="flex items-center gap-3 p-3 rounded-lg border cursor-pointer hover:bg-gray-50 transition"
                                   :class="recipientType === 'fournisseur' ? 'border-blue-500 bg-blue-50' : 'border-gray-200'">
                                <input type="radio" name="recipient_type" value="fournisseur" x-model="recipientType" class="text-blue-600">
                                <div>
                                    <span class="text-sm font-medium text-gray-900">Fournisseur</span>
                                    <span class="block text-xs text-gray-500">Fournisseur concerné</span>
                                </div>
                            </label>
                            @endif

                            {{-- Email personnalisé --}}
                            <label class="flex items-center gap-3 p-3 rounded-lg border cursor-pointer hover:bg-gray-50 transition"
                                   :class="recipientType === 'custom' ? 'border-blue-500 bg-blue-50' : 'border-gray-200'">
                                <input type="radio" name="recipient_type" value="custom" x-model="recipientType" class="text-blue-600">
                                <div class="flex-1">
                                    <span class="text-sm font-medium text-gray-900">Autre email</span>
                                </div>
                            </label>

                            <div x-show="recipientType === 'custom'" x-transition class="pl-8">
                                <input type="email" name="email" placeholder="exemple@email.com"
                                       class="w-full rounded-lg border-gray-300 text-sm focus:ring-blue-500 focus:border-blue-500">
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4 border-t">
                        <button type="button" @click="showEmailModal = false"
                                class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                            Annuler
                        </button>
                        <button type="submit"
                                class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700">
                            Envoyer
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
