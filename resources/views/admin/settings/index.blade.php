<x-layouts.dashboard :title="'Website Settings'" subtitle="Super Admin — branding, SEO, maps, SMTP, and integrations">
    <x-ui.breadcrumbs :items="[
        ['label' => 'Dashboard', 'url' => route('dashboard.index')],
        ['label' => 'Website Settings'],
    ]" />

    <div class="mx-auto max-w-6xl space-y-5">
        <div>
            <h2 class="text-lg font-semibold text-slate-900">Website Settings</h2>
            <p class="text-sm text-slate-600">Configure branding, SEO meta tags, Google Maps, SMTP email, and global business defaults. Office Manager access only.</p>
        </div>

        <div id="settings-alert" class="hidden"></div>

        <form id="settings-form" class="space-y-6" enctype="multipart/form-data">
            @csrf
            @method('PATCH')

            <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <h3 class="text-base font-semibold text-slate-900">Website branding</h3>
                <p class="mt-1 text-sm text-slate-600">Shown in the sidebar, mobile header, and login screen.</p>
                <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2">
                    <x-ui.input label="Site name" name="site_name" :value="$settings['site_name'] ?? config('app.name')" required />
                    <x-ui.input label="Tagline" name="site_tagline" :value="$settings['site_tagline'] ?? 'Operations hub'" />
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Logo image</label>
                        <input type="file" name="site_logo" accept="image/jpeg,image/png,image/webp,image/svg+xml" class="block w-full text-sm text-slate-600 file:mr-3 file:rounded-md file:border-0 file:bg-emerald-50 file:px-3 file:py-2 file:text-sm file:font-medium file:text-emerald-700 hover:file:bg-emerald-100">
                        <p class="mt-1 text-xs text-slate-500">PNG, JPG, WebP, or SVG. Max 2 MB. Used in sidebar and login.</p>
                        @if (! empty($siteLogoUrl))
                            <label class="mt-2 flex items-center gap-2 text-sm text-slate-600">
                                <input type="checkbox" name="remove_site_logo" value="1" id="remove_site_logo">
                                Remove current logo
                            </label>
                        @endif
                    </div>
                    <x-ui.input label="Logo letter (fallback)" name="site_logo_initial" maxlength="1" :value="$settings['site_logo_initial'] ?? 'M'" />
                    <div class="md:col-span-2">
                        <p class="mb-2 text-xs font-medium uppercase tracking-wide text-slate-500">Preview</p>
                        <div class="flex items-center gap-3 rounded-lg border border-slate-200 bg-[#2c3344] px-4 py-3">
                            <span id="logo-preview-wrap" class="inline-flex">
                                @if (! empty($siteLogoUrl))
                                    <img src="{{ $siteLogoUrl }}" alt="Logo" class="h-10 w-10 rounded-lg object-contain bg-white/10 p-1" id="logo-preview-img">
                                    <span class="hidden h-10 w-10 items-center justify-center rounded-lg bg-emerald-500/90 text-sm font-bold text-white" id="logo-preview-letter">{{ strtoupper(substr($settings['site_logo_initial'] ?? 'M', 0, 1)) }}</span>
                                @else
                                    <img src="" alt="Logo" class="hidden h-10 w-10 rounded-lg object-contain bg-white/10 p-1" id="logo-preview-img">
                                    <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-500/90 text-sm font-bold text-white" id="logo-preview-letter">{{ strtoupper(substr($settings['site_logo_initial'] ?? 'M', 0, 1)) }}</span>
                                @endif
                            </span>
                            <div>
                                <p class="text-sm font-bold text-white" id="name-preview">{{ $settings['site_name'] ?? config('app.name') }}</p>
                                <p class="text-xs text-slate-400" id="tagline-preview">{{ $settings['site_tagline'] ?? 'Operations hub' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <h3 class="text-base font-semibold text-slate-900">SEO &amp; meta tags</h3>
                <p class="mt-1 text-sm text-slate-600">Search engines and social previews (login and app pages use these when set).</p>
                <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div class="md:col-span-2">
                        <x-ui.input label="Meta title" name="seo_meta_title" :value="$settings['seo_meta_title'] ?? ''" placeholder="e.g. Mowing CRM — Lawn care operations" />
                    </div>
                    <div class="md:col-span-2">
                        <label class="mb-1 block text-sm font-medium text-slate-700">Meta description</label>
                        <textarea name="seo_meta_description" rows="2" maxlength="320" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm" placeholder="Short description for search results">{{ $settings['seo_meta_description'] ?? '' }}</textarea>
                    </div>
                    <div class="md:col-span-2">
                        <label class="mb-1 block text-sm font-medium text-slate-700">Meta keywords (tags)</label>
                        <input
                            type="text"
                            name="seo_meta_keywords"
                            value="{{ $settings['seo_meta_keywords'] ?? '' }}"
                            placeholder="lawn care, mowing, landscaping"
                            class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm"
                        >
                        <p class="mt-1 text-xs text-slate-500">Comma-separated keywords.</p>
                    </div>
                    <x-ui.input label="Open Graph title" name="seo_og_title" :value="$settings['seo_og_title'] ?? ''" placeholder="Defaults to meta title when empty" />
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Open Graph description</label>
                        <textarea name="seo_og_description" rows="2" maxlength="320" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">{{ $settings['seo_og_description'] ?? '' }}</textarea>
                    </div>
                    <label class="flex items-center gap-2 text-sm text-slate-700 md:col-span-2">
                        <input type="checkbox" name="seo_robots_noindex" value="1" @checked(($settings['seo_robots_noindex'] ?? '0') === '1')>
                        Hide site from search engines (noindex)
                    </label>
                </div>
            </section>

            <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <h3 class="text-base font-semibold text-slate-900">Business contact</h3>
                <p class="mt-1 text-sm text-slate-600">Used on invoices, emails, and customer-facing communication.</p>
                <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2">
                    <x-ui.input label="Company name" name="company_name" :value="$settings['company_name'] ?? 'Mowing CRM'" required />
                    <x-ui.input label="Company phone" name="company_phone" :value="$settings['company_phone'] ?? ''" />
                    <x-ui.input label="Company email" name="company_email" type="email" :value="$settings['company_email'] ?? ''" />
                    <x-ui.input label="Default timezone" name="default_timezone" :value="$settings['default_timezone'] ?? 'UTC'" required />
                </div>
            </section>

            <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <h3 class="text-base font-semibold text-slate-900">Google Maps &amp; regional</h3>
                <p class="mt-1 text-sm text-slate-600">API key powers address autocomplete, map pickers on leads/customers, and the jobs routing map.</p>
                <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Map provider (jobs map)</label>
                        <select name="map_provider" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                            <option value="mapbox" @selected(($settings['map_provider'] ?? 'mapbox') === 'mapbox')>OpenStreetMap (Leaflet)</option>
                            <option value="google" @selected(($settings['map_provider'] ?? '') === 'google')>Google Maps</option>
                        </select>
                        <p class="mt-1 text-xs text-slate-500">Address autocomplete always uses Google when an API key is configured.</p>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Google Maps API key</label>
                        <input
                            type="password"
                            name="google_maps_api_key"
                            value="{{ $settings['google_maps_api_key'] ?? '' }}"
                            autocomplete="off"
                            placeholder="AIza..."
                            class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm"
                        >
                        <p class="mt-1 text-xs text-slate-500">Stored in Website Settings. Overrides <code class="rounded bg-slate-100 px-1">GOOGLE_MAPS_API_KEY</code> in .env when set.</p>
                    </div>
                    <x-ui.input label="Default currency" name="default_currency" :value="$settings['default_currency'] ?? 'USD'" required />
                </div>

                <div class="mt-4 border-t border-slate-100 pt-4">
                    <x-maps.address-picker
                        prefix="office"
                        address-name="office_address"
                        address-label="Office address"
                        placeholder="Search for your office address..."
                        :address-value="$settings['office_address'] ?? ''"
                        :latitude-value="$settings['office_latitude'] ?? null"
                        :longitude-value="$settings['office_longitude'] ?? null"
                        :show-coordinates="true"
                        map-height="240px"
                    />
                    <p class="mt-2 text-xs text-slate-500">Used as the starting point for the jobs routing map and nearest-employee assignment.</p>
                </div>
            </section>

            <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <h3 class="text-base font-semibold text-slate-900">Notifications</h3>
                <div class="mt-4 grid grid-cols-1 gap-3 md:grid-cols-2">
                    <label class="flex items-center gap-2 text-sm text-slate-700">
                        <input type="checkbox" name="notification_email_enabled" value="1" @checked(($settings['notification_email_enabled'] ?? '1') === '1')>
                        Enable email notifications
                    </label>
                    <label class="flex items-center gap-2 text-sm text-slate-700">
                        <input type="checkbox" name="notification_in_app_enabled" value="1" @checked(($settings['notification_in_app_enabled'] ?? '1') === '1')>
                        Enable in-app notifications
                    </label>
                </div>
            </section>

            <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <h3 class="text-base font-semibold text-slate-900">Email delivery (SMTP)</h3>
                <p class="mt-1 text-xs text-slate-600">
                    When enabled, these values override <code class="rounded bg-slate-100 px-1">.env</code> mail settings.
                    Leave SMTP password blank to keep the current password.
                </p>

                <label class="mt-4 mb-3 flex items-center gap-2 text-sm font-medium text-slate-800">
                    <input type="checkbox" id="mail_use_database" name="mail_use_database" value="1" @checked(($settings['mail_use_database'] ?? '0') === '1')>
                    Use database mail settings
                </label>

                <div id="mail-db-fields" class="grid grid-cols-1 gap-4 md:grid-cols-2 {{ ($settings['mail_use_database'] ?? '0') === '1' ? '' : 'hidden' }}">
                    <div class="md:col-span-2">
                        <label class="mb-1 block text-sm font-medium text-slate-700">Mailer driver</label>
                        <select name="mail_mailer" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                            <option value="smtp" @selected(($settings['mail_mailer'] ?? 'smtp') === 'smtp')>SMTP</option>
                            <option value="log" @selected(($settings['mail_mailer'] ?? '') === 'log')>Log (testing)</option>
                            <option value="array" @selected(($settings['mail_mailer'] ?? '') === 'array')>Array (testing)</option>
                        </select>
                    </div>

                    <div id="smtp-fields" class="contents">
                        <x-ui.input label="SMTP host" name="mail_host" :value="$settings['mail_host'] ?? ''" />
                        <x-ui.input label="SMTP port" name="mail_port" type="number" :value="$settings['mail_port'] ?? '587'" />
                        <x-ui.input label="SMTP username" name="mail_username" :value="$settings['mail_username'] ?? ''" />
                        <x-ui.input label="SMTP password" name="mail_password" type="password" placeholder="Leave blank to keep current" />
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Encryption</label>
                            <select name="mail_encryption" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                                <option value="" @selected(($settings['mail_encryption'] ?? '') === '')>None / STARTTLS</option>
                                <option value="tls" @selected(($settings['mail_encryption'] ?? '') === 'tls')>TLS</option>
                                <option value="ssl" @selected(($settings['mail_encryption'] ?? '') === 'ssl')>SSL</option>
                            </select>
                        </div>
                    </div>

                    <x-ui.input label="From email address" name="mail_from_address" type="email" :value="$settings['mail_from_address'] ?? ''" />
                    <x-ui.input label="From name" name="mail_from_name" :value="$settings['mail_from_name'] ?? ($settings['site_name'] ?? config('app.name'))" />
                </div>
            </section>

            <x-ui.button type="submit">Save website settings</x-ui.button>
        </form>

        {{-- Database Backups --}}
        <div class="mt-8 space-y-5">
            <div>
                <h2 class="text-lg font-semibold text-slate-900">Database Backups</h2>
                <p class="text-sm text-slate-600">Configure automatic backups and manage your backup history.</p>
            </div>

            <form id="backup-settings-form" class="space-y-0">
                @csrf
                @method('PATCH')
                <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <h3 class="text-base font-semibold text-slate-900">Backup configuration</h3>
                    <p class="mt-1 text-sm text-slate-600">Backups run automatically at 2:00 AM on your configured schedule. You can also trigger one manually below.</p>

                    <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Backup schedule</label>
                            <select name="backup_schedule" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                                <option value="weekly" @selected(($settings['backup_schedule'] ?? 'weekly') === 'weekly')>Weekly (every 7 days)</option>
                                <option value="biweekly" @selected(($settings['backup_schedule'] ?? '') === 'biweekly')>Biweekly (every 14 days)</option>
                                <option value="monthly" @selected(($settings['backup_schedule'] ?? '') === 'monthly')>Monthly (every 30 days)</option>
                            </select>
                        </div>

                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Storage disk</label>
                            <select name="backup_disk" id="backup_disk" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                                <option value="local" @selected(($settings['backup_disk'] ?? 'local') === 'local')>Local (server filesystem)</option>
                                <option value="gcs" @selected(($settings['backup_disk'] ?? '') === 'gcs')>Google Cloud Storage</option>
                            </select>
                        </div>

                        <div id="gcs-fields" class="contents {{ ($settings['backup_disk'] ?? 'local') === 'gcs' ? '' : 'hidden' }}">
                            <x-ui.input label="GCS bucket name" name="backup_gcs_bucket" :value="$settings['backup_gcs_bucket'] ?? ''" placeholder="my-backup-bucket" />
                            <x-ui.input label="GCS project ID" name="backup_gcs_project" :value="$settings['backup_gcs_project'] ?? ''" placeholder="my-gcp-project-id" />
                            <div class="md:col-span-2">
                                <x-ui.input label="Service account key file path" name="backup_gcs_key_file" :value="$settings['backup_gcs_key_file'] ?? ''" placeholder="/path/to/service-account.json" />
                                <p class="mt-1 text-xs text-slate-500">Absolute path to your GCP service account JSON key file on the server. Leave empty to use <code class="rounded bg-slate-100 px-1">GOOGLE_APPLICATION_CREDENTIALS</code> env var.</p>
                            </div>
                        </div>

                        <div class="md:col-span-2">
                            <x-ui.input label="mysqldump path (MySQL only)" name="backup_mysqldump_path" :value="$settings['backup_mysqldump_path'] ?? ''" placeholder="mysqldump" />
                            <p class="mt-1 text-xs text-slate-500">Leave blank to use the system PATH. Set if mysqldump is not in PATH (e.g. <code class="rounded bg-slate-100 px-1">C:\laragon\bin\mysql\mysql-8.0.30-winx64\bin\mysqldump.exe</code>).</p>
                        </div>
                    </div>

                    <div class="mt-4 flex flex-wrap items-center gap-3">
                        <x-ui.button type="button" id="save-backup-settings-btn">Save backup settings</x-ui.button>
                        <button
                            type="button"
                            id="trigger-backup-btn"
                            class="inline-flex items-center gap-2 rounded-md border border-emerald-600 bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700 disabled:opacity-60"
                        >
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                            Trigger backup now
                        </button>
                    </div>

                    <div id="backup-alert" class="mt-3 hidden"></div>
                </section>
            </form>

            {{-- Backup list --}}
            <section class="rounded-2xl border border-slate-200 bg-white shadow-sm" id="backup-list-section">
                <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
                    <h3 class="text-base font-semibold text-slate-900">Backup history</h3>
                    <span class="text-xs text-slate-400" id="backup-poll-indicator"></span>
                </div>

                <div id="backup-list-wrap">
                    @if ($backups->isEmpty())
                        <p class="px-5 py-8 text-center text-sm text-slate-500" id="backup-empty-msg">No backups yet. Trigger one manually or wait for the schedule.</p>
                    @else
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead class="border-b border-slate-200 bg-slate-50 text-left text-xs font-medium uppercase tracking-wide text-slate-500">
                                    <tr>
                                        <th class="px-5 py-3">Filename</th>
                                        <th class="px-5 py-3">Disk</th>
                                        <th class="px-5 py-3">Size</th>
                                        <th class="px-5 py-3">Status</th>
                                        <th class="px-5 py-3">Triggered</th>
                                        <th class="px-5 py-3">Date</th>
                                        <th class="px-5 py-3 text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100" id="backup-table-body">
                                    @foreach ($backups as $backup)
                                        <tr class="hover:bg-slate-50" data-backup-id="{{ $backup->id }}">
                                            <td class="px-5 py-3 font-mono text-xs text-slate-700">{{ $backup->filename }}</td>
                                            <td class="px-5 py-3 text-slate-600">{{ $backup->disk }}</td>
                                            <td class="px-5 py-3 text-slate-600">{{ $backup->formattedSize() }}</td>
                                            <td class="px-5 py-3">
                                                <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium {{ $backup->status->badgeClass() }}">
                                                    {{ $backup->status->label() }}
                                                </span>
                                                @if ($backup->error_message)
                                                    <span class="ml-1 text-xs text-red-500" title="{{ $backup->error_message }}">&#9432;</span>
                                                @endif
                                            </td>
                                            <td class="px-5 py-3 text-slate-600">{{ $backup->actor?->name ?? 'Scheduled' }}</td>
                                            <td class="px-5 py-3 text-slate-600">{{ $backup->created_at->format('d M Y H:i') }}</td>
                                            <td class="px-5 py-3 text-right">
                                                <div class="flex items-center justify-end gap-2">
                                                    @if ($backup->status->value === 'completed')
                                                        <a
                                                            href="{{ route('admin.settings.backups.download', $backup) }}"
                                                            class="text-xs font-medium text-emerald-600 hover:text-emerald-800"
                                                        >Download</a>
                                                    @endif
                                                    <button
                                                        type="button"
                                                        class="delete-backup-btn text-xs font-medium text-red-500 hover:text-red-700"
                                                        data-id="{{ $backup->id }}"
                                                        data-url="{{ route('admin.settings.backups.destroy', $backup) }}"
                                                    >Delete</button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </section>
        </div>
    </div>

    <script>
        function showSettingsAlert(message, isError = false) {
            const baseClass = isError
                ? 'rounded-md border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700'
                : 'rounded-md border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-700';
            $('#settings-alert').removeClass('hidden').attr('class', baseClass).text(message);
        }

        function updateBrandingPreview() {
            const name = $('[name="site_name"]').val() || 'Mowing CRM';
            const tagline = $('[name="site_tagline"]').val() || 'Operations hub';
            const letter = ($('[name="site_logo_initial"]').val() || 'M').charAt(0).toUpperCase();
            $('#name-preview').text(name);
            $('#tagline-preview').text(tagline);
            $('#logo-preview-letter').text(letter);
        }

        function previewLogoFile(file) {
            if (!file || !file.type.startsWith('image/')) {
                return;
            }
            const reader = new FileReader();
            reader.onload = function (e) {
                $('#logo-preview-img').attr('src', e.target.result).removeClass('hidden');
                $('#logo-preview-letter').addClass('hidden');
            };
            reader.readAsDataURL(file);
        }

        $('[name="site_name"], [name="site_tagline"], [name="site_logo_initial"]').on('input', updateBrandingPreview);
        $('[name="site_logo"]').on('change', function () {
            previewLogoFile(this.files[0]);
        });

        $('#settings-form').on('submit', function (e) {
            e.preventDefault();
            const $form = $(this);
            const formData = new FormData();
            formData.append('_token', $form.find('input[name="_token"]').val());
            formData.append('_method', 'PATCH');
            formData.append('site_name', $form.find('[name="site_name"]').val());
            formData.append('site_tagline', $form.find('[name="site_tagline"]').val());
            formData.append('site_logo_initial', $form.find('[name="site_logo_initial"]').val());
            formData.append('company_name', $form.find('[name="company_name"]').val());
            formData.append('company_phone', $form.find('[name="company_phone"]').val());
            formData.append('company_email', $form.find('[name="company_email"]').val());
            formData.append('default_timezone', $form.find('[name="default_timezone"]').val());
            formData.append('map_provider', $form.find('[name="map_provider"]').val());
            formData.append('google_maps_api_key', $form.find('[name="google_maps_api_key"]').val());
            formData.append('office_address', $('#office-address-input').val());
            formData.append('office_latitude', $('#office-latitude').val());
            formData.append('office_longitude', $('#office-longitude').val());
            formData.append('default_currency', $form.find('[name="default_currency"]').val());
            formData.append('seo_meta_title', $form.find('[name="seo_meta_title"]').val());
            formData.append('seo_meta_description', $form.find('[name="seo_meta_description"]').val());
            formData.append('seo_meta_keywords', $form.find('[name="seo_meta_keywords"]').val());
            formData.append('seo_og_title', $form.find('[name="seo_og_title"]').val());
            formData.append('seo_og_description', $form.find('[name="seo_og_description"]').val());
            formData.append('seo_robots_noindex', $form.find('[name="seo_robots_noindex"]').is(':checked') ? 1 : 0);
            formData.append('notification_email_enabled', $form.find('[name="notification_email_enabled"]').is(':checked') ? 1 : 0);
            formData.append('notification_in_app_enabled', $form.find('[name="notification_in_app_enabled"]').is(':checked') ? 1 : 0);
            formData.append('mail_use_database', $form.find('[name="mail_use_database"]').is(':checked') ? 1 : 0);
            formData.append('mail_mailer', $form.find('[name="mail_mailer"]').val());
            formData.append('mail_host', $form.find('[name="mail_host"]').val());
            formData.append('mail_port', $form.find('[name="mail_port"]').val());
            formData.append('mail_username', $form.find('[name="mail_username"]').val());
            formData.append('mail_encryption', $form.find('[name="mail_encryption"]').val());
            formData.append('mail_from_address', $form.find('[name="mail_from_address"]').val());
            formData.append('mail_from_name', $form.find('[name="mail_from_name"]').val());
            const logoFile = $form.find('[name="site_logo"]')[0]?.files?.[0];
            if (logoFile) {
                formData.append('site_logo', logoFile);
            }
            if ($('#remove_site_logo').is(':checked')) {
                formData.append('remove_site_logo', 1);
            }
            const pw = $form.find('[name="mail_password"]').val();
            if (pw) {
                formData.append('mail_password', pw);
            }

            $.ajax({
                url: "{{ route('admin.settings.update') }}",
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                headers: { 'Accept': 'application/json' },
                success: function (res) {
                    showSettingsAlert(res.message || 'Website settings saved.');
                    setTimeout(() => window.location.reload(), 600);
                },
                error: function (xhr) {
                    const errors = xhr.responseJSON?.errors || {};
                    showSettingsAlert(Object.values(errors)[0]?.[0] || 'Unable to save settings.', true);
                }
            });
        });

        function toggleMailDbFields() {
            $('#mail-db-fields').toggleClass('hidden', !$('#mail_use_database').is(':checked'));
        }

        $('#mail_use_database').on('change', toggleMailDbFields);
        toggleMailDbFields();

        // Backup settings
        function showBackupAlert(message, isError = false) {
            const baseClass = isError
                ? 'rounded-md border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700'
                : 'rounded-md border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-700';
            $('#backup-alert').removeClass('hidden').attr('class', baseClass).text(message);
        }

        function toggleGcsFields() {
            const isGcs = $('#backup_disk').val() === 'gcs';
            $('#gcs-fields').toggleClass('contents', isGcs).toggleClass('hidden', !isGcs);
        }

        $('#backup_disk').on('change', toggleGcsFields);
        toggleGcsFields();

        $('#save-backup-settings-btn').on('click', function () {
            const data = new FormData();
            data.append('_token', $('[name="_token"]').first().val());
            data.append('_method', 'PATCH');
            // Pass existing required fields to satisfy validation
            data.append('site_name', $('[name="site_name"]').val());
            data.append('company_name', $('[name="company_name"]').val());
            data.append('default_timezone', $('[name="default_timezone"]').val());
            data.append('map_provider', $('[name="map_provider"]').val());
            data.append('default_currency', $('[name="default_currency"]').val());
            data.append('notification_email_enabled', $('[name="notification_email_enabled"]').is(':checked') ? 1 : 0);
            data.append('notification_in_app_enabled', $('[name="notification_in_app_enabled"]').is(':checked') ? 1 : 0);
            data.append('seo_robots_noindex', $('[name="seo_robots_noindex"]').is(':checked') ? 1 : 0);
            data.append('mail_use_database', $('[name="mail_use_database"]').is(':checked') ? 1 : 0);
            // Backup-specific fields
            data.append('backup_schedule', $('[name="backup_schedule"]').val());
            data.append('backup_disk', $('[name="backup_disk"]').val());
            data.append('backup_gcs_bucket', $('[name="backup_gcs_bucket"]').val() || '');
            data.append('backup_gcs_project', $('[name="backup_gcs_project"]').val() || '');
            data.append('backup_gcs_key_file', $('[name="backup_gcs_key_file"]').val() || '');
            data.append('backup_mysqldump_path', $('[name="backup_mysqldump_path"]').val() || '');

            $.ajax({
                url: "{{ route('admin.settings.update') }}",
                method: 'POST',
                data: data,
                processData: false,
                contentType: false,
                headers: { 'Accept': 'application/json' },
                success: function (res) {
                    showBackupAlert(res.message || 'Backup settings saved.');
                },
                error: function (xhr) {
                    const errors = xhr.responseJSON?.errors || {};
                    showBackupAlert(Object.values(errors)[0]?.[0] || 'Unable to save backup settings.', true);
                }
            });
        });

        $('#trigger-backup-btn').on('click', function () {
            const $btn = $(this);
            $btn.prop('disabled', true).text('Queuing…');

            $.ajax({
                url: "{{ route('admin.settings.backups.trigger') }}",
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('[name="_token"]').first().val(),
                    'Accept': 'application/json',
                },
                success: function (res) {
                    showBackupAlert(res.message || 'Backup queued.');
                    $btn.prop('disabled', false).html('<svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg> Trigger backup now');
                    pollBackups();
                },
                error: function (xhr) {
                    showBackupAlert(xhr.responseJSON?.message || 'Failed to trigger backup.', true);
                    $btn.prop('disabled', false).html('<svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg> Trigger backup now');
                }
            });
        });

        $(document).on('click', '.delete-backup-btn', function () {
            if (! confirm('Delete this backup? This cannot be undone.')) {
                return;
            }

            const $btn = $(this);
            const url = $btn.data('url');
            const id = $btn.data('id');

            $.ajax({
                url: url,
                method: 'POST',
                data: { _method: 'DELETE', _token: $('[name="_token"]').first().val() },
                success: function () {
                    $(`tr[data-backup-id="${id}"]`).fadeOut(300, function () { $(this).remove(); });
                },
                error: function () {
                    alert('Failed to delete backup.');
                }
            });
        });

        // Backup list polling — refreshes every 10s while any backup is pending/running
        const BACKUP_LIST_URL = "{{ route('admin.settings.backups.list') }}";
        let backupPollTimer = null;

        function backupRowHtml(b) {
            const downloadLink = b.download_url
                ? `<a href="${b.download_url}" class="text-xs font-medium text-emerald-600 hover:text-emerald-800">Download</a>`
                : '';
            const errorIcon = b.error_message
                ? `<span class="ml-1 text-xs text-red-500" title="${$('<div>').text(b.error_message).html()}">&#9432;</span>`
                : '';
            return `<tr class="hover:bg-slate-50" data-backup-id="${b.id}">
                <td class="px-5 py-3 font-mono text-xs text-slate-700">${$('<div>').text(b.filename).html()}</td>
                <td class="px-5 py-3 text-slate-600">${b.disk}</td>
                <td class="px-5 py-3 text-slate-600">${b.size}</td>
                <td class="px-5 py-3">
                    <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium ${b.status_badge}">${b.status_label}</span>${errorIcon}
                </td>
                <td class="px-5 py-3 text-slate-600">${$('<div>').text(b.actor).html()}</td>
                <td class="px-5 py-3 text-slate-600">${b.created_at}</td>
                <td class="px-5 py-3 text-right">
                    <div class="flex items-center justify-end gap-2">
                        ${downloadLink}
                        <button type="button" class="delete-backup-btn text-xs font-medium text-red-500 hover:text-red-700"
                            data-id="${b.id}" data-url="${b.delete_url}">Delete</button>
                    </div>
                </td>
            </tr>`;
        }

        function renderBackupList(backups) {
            const $wrap = $('#backup-list-wrap');

            if (backups.length === 0) {
                $wrap.html('<p class="px-5 py-8 text-center text-sm text-slate-500">No backups yet. Trigger one manually or wait for the schedule.</p>');
                return;
            }

            const hasTable = $wrap.find('table').length > 0;
            if (!hasTable) {
                $wrap.html(`<div class="overflow-x-auto"><table class="w-full text-sm">
                    <thead class="border-b border-slate-200 bg-slate-50 text-left text-xs font-medium uppercase tracking-wide text-slate-500">
                        <tr>
                            <th class="px-5 py-3">Filename</th><th class="px-5 py-3">Disk</th>
                            <th class="px-5 py-3">Size</th><th class="px-5 py-3">Status</th>
                            <th class="px-5 py-3">Triggered</th><th class="px-5 py-3">Date</th>
                            <th class="px-5 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100" id="backup-table-body"></tbody>
                </table></div>`);
            }

            const $tbody = $('#backup-table-body');
            backups.forEach(function (b) {
                const $existing = $tbody.find(`tr[data-backup-id="${b.id}"]`);
                if ($existing.length) {
                    $existing.replaceWith(backupRowHtml(b));
                } else {
                    $tbody.prepend(backupRowHtml(b));
                }
            });
        }

        function hasActiveBackups(backups) {
            return backups.some(b => b.status === 'pending' || b.status === 'running');
        }

        function pollBackups() {
            $.getJSON(BACKUP_LIST_URL, function (data) {
                renderBackupList(data.backups);

                if (hasActiveBackups(data.backups)) {
                    $('#backup-poll-indicator').text('Auto-refreshing…');
                    backupPollTimer = setTimeout(pollBackups, 10000);
                } else {
                    $('#backup-poll-indicator').text('');
                    backupPollTimer = null;
                }
            });
        }

        function startPollingIfNeeded() {
            if (backupPollTimer) {
                return;
            }
            const activeRows = $('#backup-table-body tr').filter(function () {
                const status = $(this).find('span.rounded-full').text().trim().toLowerCase();
                return status === 'pending' || status === 'running';
            });
            if (activeRows.length > 0) {
                $('#backup-poll-indicator').text('Auto-refreshing…');
                backupPollTimer = setTimeout(pollBackups, 10000);
            }
        }

        startPollingIfNeeded();
    </script>
</x-layouts.dashboard>
