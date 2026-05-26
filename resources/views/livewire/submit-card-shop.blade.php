<div class="max-w-2xl mx-auto py-10 px-4">

    <a href="{{ route('shops.index') }}" class="inline-flex items-center gap-1 text-sm text-gray-400 hover:text-gray-600 mb-6">
        <x-heroicon-o-arrow-left class="size-4" /> Card Shops
    </a>

    @if ($submitted)
        <div class="bg-white border border-gray-100 rounded-2xl p-10 text-center">
            <x-heroicon-o-check-circle class="size-12 text-green-500 mx-auto mb-4" />
            <h2 class="text-xl font-bold text-gray-900 mb-2">Shop Submitted!</h2>
            <p class="text-gray-500 text-sm mb-6">Your shop listing is under review. We'll notify you once it's approved.</p>
            <a href="{{ route('shops.index') }}"
               class="px-6 py-2 text-sm font-semibold text-white rounded-xl"
               style="background-color:#D93C3F;">
                Browse Card Shops
            </a>
        </div>
    @else

    <h1 class="text-2xl font-bold text-gray-900 mb-1">Add a Card Shop</h1>
    <p class="text-sm text-gray-500 mb-8">Submit a local card shop, dealer, or hobby store to the directory.</p>

    <div class="space-y-8">

        {{-- Basic Info --}}
        <div class="bg-white border border-gray-100 rounded-2xl p-6 space-y-4">
            <h2 class="text-sm font-bold uppercase tracking-widest text-gray-400">Shop Info</h2>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Shop Name <span class="text-[#D93C3F]">*</span></label>
                <input type="text" wire:model="name"
                       class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#D93C3F]/30" />
                @error('name') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Owner / Manager Name</label>
                <input type="text" wire:model="ownerName"
                       class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#D93C3F]/30" />
            </div>

            {{-- Logo --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Shop Logo</label>
                <p class="text-xs text-gray-400 mb-2">Your shop's logo or brand mark. Shown as the shop icon throughout the directory.</p>
                <div x-data="{ preview: null }" wire:ignore>
                    <label class="flex flex-col items-center justify-center w-full h-28 border-2 border-dashed border-gray-200 rounded-xl cursor-pointer hover:border-gray-300 hover:bg-gray-50 transition-colors">
                        <img x-show="preview" :src="preview" class="h-full w-full object-contain rounded-xl p-2" />
                        <div x-show="!preview" class="flex flex-col items-center gap-1 text-gray-400">
                            <x-heroicon-o-building-storefront class="size-7" />
                            <span class="text-sm">Upload logo</span>
                            <span class="text-xs">JPG, PNG, WEBP up to 5MB</span>
                        </div>
                        <input type="file" wire:model="logo"
                               @change="preview = $event.target.files[0] ? URL.createObjectURL($event.target.files[0]) : null"
                               accept="image/*" class="hidden" />
                    </label>
                    <div wire:loading wire:target="logo" class="text-xs text-gray-400 mt-1">Uploading…</div>
                </div>
                @error('logo') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Store Photo --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Store Photo</label>
                <p class="text-xs text-gray-400 mb-2">A photo of your storefront, interior, or display cases. Used as the hero image on your listing.</p>
                <div x-data="{ preview: null }" wire:ignore>
                    <label class="flex flex-col items-center justify-center w-full h-36 border-2 border-dashed border-gray-200 rounded-xl cursor-pointer hover:border-gray-300 hover:bg-gray-50 transition-colors overflow-hidden">
                        <img x-show="preview" :src="preview" class="h-full w-full object-cover" />
                        <div x-show="!preview" class="flex flex-col items-center gap-1 text-gray-400">
                            <x-heroicon-o-camera class="size-7" />
                            <span class="text-sm">Upload store photo</span>
                            <span class="text-xs">JPG, PNG, WEBP up to 5MB</span>
                        </div>
                        <input type="file" wire:model="storePhoto"
                               @change="preview = $event.target.files[0] ? URL.createObjectURL($event.target.files[0]) : null"
                               accept="image/*" class="hidden" />
                    </label>
                    <div wire:loading wire:target="storePhoto" class="text-xs text-gray-400 mt-1">Uploading…</div>
                </div>
                @error('storePhoto') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                <textarea wire:model="description" rows="3"
                          placeholder="Tell collectors about your shop, specialties, inventory…"
                          class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#D93C3F]/30"></textarea>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Specialty Categories</label>
                <div class="flex flex-wrap gap-2">
                    @foreach ($this->categories as $cat)
                        <label class="flex items-center gap-1.5 cursor-pointer">
                            <input type="checkbox" wire:model="selectedCategories" value="{{ $cat->id }}"
                                   class="rounded text-[#D93C3F] focus:ring-[#D93C3F]/30" />
                            <span class="text-sm text-gray-700">{{ $cat->name }}</span>
                        </label>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Location --}}
        <div class="bg-white border border-gray-100 rounded-2xl p-6 space-y-4">
            <h2 class="text-sm font-bold uppercase tracking-widest text-gray-400">Location</h2>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Street Address</label>
                <input type="text" wire:model="address"
                       class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#D93C3F]/30" />
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">City <span class="text-[#D93C3F]">*</span></label>
                    <input type="text" wire:model="city"
                           class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#D93C3F]/30" />
                    @error('city') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        State / Province <span class="text-[#D93C3F]">*</span>
                    </label>
                    @if ($this->states)
                        <select wire:model="state"
                                class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#D93C3F]/30">
                            <option value="">Select…</option>
                            @foreach ($this->states as $abbr => $label)
                                <option value="{{ $abbr }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    @else
                        <p class="text-xs text-gray-400 py-2">State/province selection is only supported for US and Canadian shops.</p>
                    @endif
                    @error('state') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Postal Code</label>
                    <input type="text" wire:model="zipCode" maxlength="10"
                           class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#D93C3F]/30" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                    <input type="text" wire:model="phone" placeholder="(555) 555-5555"
                           class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#D93C3F]/30" />
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Country <span class="text-[#D93C3F]">*</span></label>
                <select wire:model="country"
                        class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#D93C3F]/30">
                    @foreach ($this->countries as $code => $name)
                        <option value="{{ $code }}">{{ $name }}</option>
                    @endforeach
                </select>
                @error('country') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Website</label>
                <input type="url" wire:model="website" placeholder="https://…"
                       class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#D93C3F]/30" />
                @error('website') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        {{-- Hours --}}
        <div class="bg-white border border-gray-100 rounded-2xl p-6">
            <h2 class="text-sm font-bold uppercase tracking-widest text-gray-400 mb-4">Hours of Operation</h2>
            <div class="space-y-2">
                @foreach (['monday','tuesday','wednesday','thursday','friday','saturday','sunday'] as $day)
                    <div class="flex items-center gap-3">
                        <span class="w-24 text-sm font-medium text-gray-700">{{ ucfirst($day) }}</span>
                        <input type="time" wire:model="hours.{{ $day }}.open"
                               class="border border-gray-200 rounded-lg px-2 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#D93C3F]/30" />
                        <span class="text-gray-400 text-sm">–</span>
                        <input type="time" wire:model="hours.{{ $day }}.close"
                               class="border border-gray-200 rounded-lg px-2 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#D93C3F]/30" />
                        <span class="text-xs text-gray-400">(leave blank if closed)</span>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Submit --}}
        <div class="flex justify-end">
            <button wire:click="submit" wire:loading.attr="disabled"
                    class="px-6 py-2.5 text-sm font-semibold text-white rounded-xl transition-opacity"
                    style="background-color:#D93C3F;">
                <span wire:loading.remove>Submit Shop</span>
                <span wire:loading>Submitting…</span>
            </button>
        </div>

    </div>
    @endif

</div>
