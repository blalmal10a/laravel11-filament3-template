<?php

namespace App\Filament\Forms\Components;

use Closure;
use Exception;
use Filament\Forms\Components\BaseFileUpload;
use Filament\Forms\Components\Concerns\HasExtraInputAttributes;
use Filament\Forms\Components\Concerns\HasPlaceholder;
use Filament\Forms\Components\FileUpload;
use Filament\Support\Concerns\HasAlignment;
use Filament\Support\Concerns\HasExtraAlpineAttributes;
use Filament\Support\Facades\FilamentIcon;
use Illuminate\Support\Collection;
use Illuminate\Support\HtmlString;
use League\Flysystem\UnableToCheckFileExistence;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Illuminate\Support\Str;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class CustomFileUpload extends BaseFileUpload
{
    use HasExtraInputAttributes;
    use HasPlaceholder;
    use HasAlignment;
    use HasExtraAlpineAttributes;

    /**
     * @var view-string
     */
    protected string $view = 'filament-forms::components.file-upload';

    protected string | Closure | null $imageCropAspectRatio = null;

    protected string | Closure | null $imagePreviewHeight = null;

    protected string | Closure | null $imageResizeTargetHeight = null;

    protected string | Closure | null $imageResizeTargetWidth = null;

    protected string | Closure | null $imageResizeMode = null;

    protected bool | Closure $imageResizeUpscale = true;

    protected bool | Closure $isAvatar = false;

    protected int | float | Closure | null $itemPanelAspectRatio = null;

    protected string | Closure $loadingIndicatorPosition = 'right';

    protected string | Closure | null $panelAspectRatio = null;

    protected string | Closure | null $panelLayout = 'compact';

    protected string | Closure $removeUploadedFileButtonPosition = 'left';

    protected bool | Closure $shouldAppendFiles = false;

    protected bool | Closure $shouldOrientImagesFromExif = true;

    protected string | Closure $uploadButtonPosition = 'right';

    protected string | Closure $uploadProgressIndicatorPosition = 'right';

    protected bool | Closure $hasImageEditor = false;

    protected bool | Closure $hasCircleCropper = false;

    protected bool | Closure $canEditSvgs = true;

    protected bool | Closure $isSvgEditingConfirmed = false;

    protected int | Closure | null $imageEditorViewportWidth = null;

    protected int | Closure | null $imageEditorViewportHeight = null;

    protected int $imageEditorMode = 1;

    protected string | Closure | null $imageEditorEmptyFillColor = null;

    /**
     * @var array<?string> | Closure
     */
    protected array | Closure $imageEditorAspectRatios = [];

    protected array | Closure $mimeTypeMap = [];

    // custom
    protected ?Closure $getUploadedFileUsing = null;

    protected ?Closure $reorderUploadedFilesUsing = null;

    protected ?Closure $saveUploadedFileUsing = null;
    // end custom

    protected function setUp(): void
    {
        parent::setUp();
        $this->afterStateHydrated(static function (BaseFileUpload $component, string | array | null $state): void {
            if (blank($state)) {
                $component->state([]);
                return;
            }

            $files = collect(Arr::wrap($state))
                ->filter(static function (string $file) use ($component): bool {
                    if (blank($file)) {
                        return false;
                    }

                    // If it's a URL (starts with http:// or https://), consider it valid
                    if (Str::startsWith($file, ['http://', 'https://'])) {
                        return true;
                    }

                    // Otherwise, check if file exists in local storage
                    try {
                        return $component->getDisk()->exists($file);
                    } catch (UnableToCheckFileExistence $exception) {
                        return false;
                    }
                })
                ->mapWithKeys(static fn(string $file): array => [((string) Str::uuid()) => $file])
                ->all();

            $component->state($files);
        });

        $this->afterStateUpdated(static function (BaseFileUpload $component, $state) {
            // if ($state instanceof TemporaryUploadedFile) {
            //     return;
            // }

            // if (blank($state)) {
            //     return;
            // }

            // if (is_array($state)) {
            //     return;
            // }

            // $component->state([(string) Str::uuid() => $state]);
        });

        $this->beforeStateDehydrated(static function (BaseFileUpload $component): void {
            $component->saveUploadedFiles();
        });

        $this->dehydrateStateUsing(static function (BaseFileUpload $component, ?array $state): string | array | null | TemporaryUploadedFile {
            $files = array_values($state ?? []);

            if ($component->isMultiple()) {
                return $files;
            }

            return $files[0] ?? null;
        });

        $this->getUploadedFileUsing(static function (BaseFileUpload $component, string $file, string | array | null $storedFileNames): ?array {
            // If it's a URL (e.g., Imgur URL), return the file info directly
            if (Str::startsWith($file, ['http://', 'https://'])) {
                return [
                    'name' => ($component->isMultiple() ? ($storedFileNames[$file] ?? null) : $storedFileNames) ?? basename($file),
                    'size' => 0, // Size cannot be determined for external URLs
                    'type' => 'image/*', // Assume it's an image since it's from Imgur
                    'url' => $file,
                ];
            }

            /** @var FilesystemAdapter $storage */
            $storage = $component->getDisk();

            $shouldFetchFileInformation = $component->shouldFetchFileInformation();

            if ($shouldFetchFileInformation) {
                try {
                    if (! $storage->exists($file)) {
                        return null;
                    }
                } catch (UnableToCheckFileExistence $exception) {
                    return null;
                }
            }

            $url = null;

            if ($component->getVisibility() === 'private') {
                try {
                    $url = $storage->temporaryUrl(
                        $file,
                        now()->addMinutes(5),
                    );
                } catch (Throwable $exception) {
                    // This driver does not support creating temporary URLs.
                }
            }

            $url ??= $storage->url($file);

            return [
                'name' => ($component->isMultiple() ? ($storedFileNames[$file] ?? null) : $storedFileNames) ?? basename($file),
                'size' => $shouldFetchFileInformation ? $storage->size($file) : 0,
                'type' => $shouldFetchFileInformation ? $storage->mimeType($file) : null,
                'url' => $url,
            ];
        });

        $this->getUploadedFileNameForStorageUsing(static function (BaseFileUpload $component, TemporaryUploadedFile $file) {
            return $component->shouldPreserveFilenames() ? $file->getClientOriginalName() : (Str::ulid() . '.' . $file->getClientOriginalExtension());
        });

        // customize the default save logic and use imgur
        $this->saveUploadedFileUsing(function (CustomFileUpload $component, TemporaryUploadedFile $file): ?string {
            try {
                if (!$file->exists()) {
                    return null;
                }

                // Read file contents
                $fileContents = file_get_contents($file->getRealPath());

                // Upload to Imgur using Bearer token
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . env('IMGUR_ACCESS_TOKEN'),
                ])->post('https://api.imgur.com/3/image', [
                    'image' => base64_encode($fileContents),
                    'type' => 'base64',
                    'name' => $file->getClientOriginalName(),
                ]);

                if (!$response->successful()) {
                    // Log the error response for debugging
                    logger('Imgur api success: ' . $response->body());
                    throw new \Exception('Failed to upload to Imgur: ' . $response->body());
                } else {
                    logger('Imgur API Error: ' . $response->body());
                }

                $data = $response->json();

                // Return the Imgur URL as the stored file path
                $link =  $data['data']['link'];
                logger($link);
                return $link;
            } catch (\Exception $e) {
                // Log the error and return null to indicate failure
                Log::error('Imgur upload failed: ' . $e->getMessage());
                return null;
            }
        });
    }


    public function appendFiles(bool | Closure $condition = true): static
    {
        $this->shouldAppendFiles = $condition;

        return $this;
    }

    public function avatar(): static
    {
        $this->isAvatar = true;

        $this->image();
        $this->imageResizeMode('cover');
        $this->imageResizeUpscale(false);
        $this->imageCropAspectRatio('1:1');
        $this->imageResizeTargetHeight('500');
        $this->imageResizeTargetWidth('500');
        $this->loadingIndicatorPosition('center bottom');
        $this->panelLayout('compact circle');
        $this->removeUploadedFileButtonPosition(fn(FileUpload $component) => $component->hasImageEditor() ? 'left bottom' : 'center bottom');
        $this->uploadButtonPosition(fn(FileUpload $component) => $component->hasImageEditor() ? 'right bottom' : 'center bottom');
        $this->uploadProgressIndicatorPosition(fn(FileUpload $component) => $component->hasImageEditor() ? 'right bottom' : 'center bottom');

        return $this;
    }

    /**
     * @deprecated Use `placeholder()` instead.
     */
    public function idleLabel(string | Closure | null $label): static
    {
        $this->placeholder($label);

        return $this;
    }

    public function image(): static
    {
        $this->acceptedFileTypes([
            'image/*',
        ]);

        return $this;
    }

    public function imageCropAspectRatio(string | Closure | null $ratio): static
    {
        $this->imageCropAspectRatio = $ratio;

        return $this;
    }

    public function imagePreviewHeight(string | Closure | null $height): static
    {
        $this->imagePreviewHeight = $height;

        return $this;
    }

    public function imageResizeTargetHeight(string | Closure | null $height): static
    {
        $this->imageResizeTargetHeight = $height;

        return $this;
    }

    public function imageResizeTargetWidth(string | Closure | null $width): static
    {
        $this->imageResizeTargetWidth = $width;

        return $this;
    }

    public function imageResizeMode(string | Closure | null $mode): static
    {
        $this->imageResizeMode = $mode;

        return $this;
    }

    public function imageResizeUpscale(bool | Closure $condition = true): static
    {
        $this->imageResizeUpscale = $condition;

        return $this;
    }

    public function itemPanelAspectRatio(int | float | Closure | null $ratio): static
    {
        $this->itemPanelAspectRatio = $ratio;

        return $this;
    }

    public function loadingIndicatorPosition(string | Closure | null $position): static
    {
        $this->loadingIndicatorPosition = $position;

        return $this;
    }

    public function orientImagesFromExif(bool | Closure $condition = true): static
    {
        $this->shouldOrientImagesFromExif = $condition;

        return $this;
    }

    /**
     * @deprecated Use `orientImagesFromExif()` instead.
     */
    public function orientImageFromExif(bool | Closure $condition = true): static
    {
        $this->orientImagesFromExif($condition);

        return $this;
    }

    public function panelAspectRatio(string | Closure | null $ratio): static
    {
        $this->panelAspectRatio = $ratio;

        return $this;
    }

    public function panelLayout(string | Closure | null $layout): static
    {
        $this->panelLayout = $layout;

        return $this;
    }

    public function removeUploadedFileButtonPosition(string | Closure | null $position): static
    {
        $this->removeUploadedFileButtonPosition = $position;

        return $this;
    }

    public function uploadButtonPosition(string | Closure | null $position): static
    {
        $this->uploadButtonPosition = $position;

        return $this;
    }

    public function uploadProgressIndicatorPosition(string | Closure | null $position): static
    {
        $this->uploadProgressIndicatorPosition = $position;

        return $this;
    }

    public function getImageCropAspectRatio(): ?string
    {
        return $this->evaluate($this->imageCropAspectRatio);
    }

    public function getImagePreviewHeight(): ?string
    {
        return $this->evaluate($this->imagePreviewHeight);
    }

    public function getImageResizeTargetHeight(): ?string
    {
        return $this->evaluate($this->imageResizeTargetHeight);
    }

    public function getImageResizeTargetWidth(): ?string
    {
        return $this->evaluate($this->imageResizeTargetWidth);
    }

    public function getImageResizeMode(): ?string
    {
        return $this->evaluate($this->imageResizeMode);
    }

    public function getImageResizeUpscale(): bool
    {
        return (bool) $this->evaluate($this->imageResizeUpscale);
    }

    public function getItemPanelAspectRatio(): int | float | null
    {
        $itemPanelAspectRatio = $this->evaluate($this->itemPanelAspectRatio);

        if (
            ($this->getPanelLayout() === 'grid') &&
            (! $itemPanelAspectRatio)
        ) {
            return 1;
        }

        return $itemPanelAspectRatio;
    }

    public function getLoadingIndicatorPosition()
    {
        return $this->evaluate($this->loadingIndicatorPosition);
    }

    public function getPanelAspectRatio(): ?string
    {
        return $this->evaluate($this->panelAspectRatio);
    }

    public function getPanelLayout(): ?string
    {
        return $this->evaluate($this->panelLayout);
    }

    public function getRemoveUploadedFileButtonPosition()
    {
        return $this->evaluate($this->removeUploadedFileButtonPosition);
    }

    public function getUploadButtonPosition()
    {
        return $this->evaluate($this->uploadButtonPosition);
    }

    public function getUploadProgressIndicatorPosition()
    {
        return $this->evaluate($this->uploadProgressIndicatorPosition);
    }

    public function isAvatar(): bool
    {
        return (bool) $this->evaluate($this->isAvatar);
    }

    public function shouldAppendFiles(): bool
    {
        return (bool) $this->evaluate($this->shouldAppendFiles);
    }

    public function shouldOrientImagesFromExif(): bool
    {
        return (bool) $this->evaluate($this->shouldOrientImagesFromExif);
    }

    public function imageEditor(bool | Closure $condition = true): static
    {
        $this->hasImageEditor = $condition;

        return $this;
    }

    public function circleCropper(bool | Closure $condition = true): static
    {
        $this->hasCircleCropper = $condition;

        return $this;
    }

    public function editableSvgs(bool | Closure $condition = true): static
    {
        $this->canEditSvgs = $condition;

        return $this;
    }

    public function confirmSvgEditing(bool | Closure $condition = true): static
    {
        $this->isSvgEditingConfirmed = $condition;

        return $this;
    }

    public function imageEditorViewportWidth(int | Closure | null $width): static
    {
        $this->imageEditorViewportWidth = $width;

        return $this;
    }

    public function imageEditorViewportHeight(int | Closure | null $height): static
    {
        $this->imageEditorViewportHeight = $height;

        return $this;
    }

    public function imageEditorMode(int $mode): static
    {
        if (! in_array($mode, [1, 2, 3])) {
            throw new Exception("The file upload editor mode must be either 1, 2 or 3. [{$mode}] given, which is unsupported. See https://github.com/fengyuanchen/cropperjs#viewmode for more information on the available modes. Mode 0 is not supported, as it does not allow configuration via manual inputs.");
        }

        $this->imageEditorMode = $mode;

        return $this;
    }

    public function imageEditorEmptyFillColor(string | Closure | null $color): static
    {
        $this->imageEditorEmptyFillColor = $color;

        return $this;
    }

    /**
     * @param  array<?string> | Closure  $ratios
     */
    public function imageEditorAspectRatios(array | Closure $ratios): static
    {
        $this->imageEditorAspectRatios = $ratios;

        return $this;
    }

    public function getImageEditorViewportHeight(): ?int
    {
        if (($targetHeight = (int) $this->getImageResizeTargetHeight()) > 1) {
            return (int) round($targetHeight * $this->getParentTargetSizes($targetHeight), precision: 0);
        }

        if (filled($ratio = $this->getImageCropAspectRatio())) {
            [$numerator, $denominator] = explode(':', $ratio);

            return (int) $denominator;
        }

        return $this->evaluate($this->imageEditorViewportHeight);
    }

    public function getImageEditorViewportWidth(): ?int
    {
        if (($targetWidth = (int) $this->getImageResizeTargetWidth()) > 1) {
            return (int) round($targetWidth * $this->getParentTargetSizes($targetWidth), precision: 0);
        }

        if (filled($ratio = $this->getImageCropAspectRatio())) {
            [$numerator, $denominator] = explode(':', $ratio);

            return (int) $numerator;
        }

        return $this->evaluate($this->imageEditorViewportWidth);
    }

    protected function getParentTargetSizes(int $withOrHeight): float
    {
        return $withOrHeight > 1 ? 360 / (int) $this->getImageResizeTargetWidth() : 1;
    }

    public function getImageEditorMode(): int
    {
        return $this->imageEditorMode;
    }

    public function getImageEditorEmptyFillColor(): ?string
    {
        return $this->evaluate($this->imageEditorEmptyFillColor);
    }

    public function hasImageEditor(): bool
    {
        return (bool) $this->evaluate($this->hasImageEditor);
    }

    public function hasCircleCropper(): bool
    {
        return (bool) $this->evaluate($this->hasCircleCropper);
    }

    public function canEditSvgs(): bool
    {
        return (bool) $this->evaluate($this->canEditSvgs);
    }

    public function isSvgEditingConfirmed(): bool
    {
        return (bool) $this->evaluate($this->isSvgEditingConfirmed);
    }

    /**
     * @return array<string, float | string>
     */
    public function getImageEditorAspectRatiosForJs(): array
    {
        return collect($this->evaluate($this->imageEditorAspectRatios) ?? [])
            ->when(
                filled($imageCropAspectRatio = $this->getImageCropAspectRatio()),
                fn(Collection $ratios): Collection => $ratios->push($imageCropAspectRatio),
            )
            ->unique()
            ->mapWithKeys(fn(?string $ratio): array => [
                $ratio ?? __('filament-forms::components.file_upload.editor.aspect_ratios.no_fixed.label') => $this->normalizeImageCroppingRatioForJs($ratio),
            ])
            ->filter(fn(float | string | false $ratio): bool => $ratio !== false)
            ->when(
                fn(Collection $ratios): bool => $ratios->count() < 2,
                fn(Collection $ratios) => $ratios->take(0),
            )
            ->all();
    }

    protected function normalizeImageCroppingRatioForJs(?string $ratio): float | string | false
    {
        if ($ratio === null) {
            return 'NaN';
        }

        $ratioParts = explode(':', $ratio);

        if (count($ratioParts) !== 2) {
            return false;
        }

        [$numerator, $denominator] = $ratioParts;

        if (! $denominator) {
            return false;
        }

        if (! is_numeric($numerator)) {
            return false;
        }

        if (! is_numeric($denominator)) {
            return false;
        }

        return $numerator / $denominator;
    }

    /**
     * @return array<array<array<string, mixed>>>
     */
    public function getImageEditorActions(string $iconSizeClasses): array
    {
        return [
            'zoom' => [
                [
                    'label' => __('filament-forms::components.file_upload.editor.actions.drag_move.label'),
                    'iconHtml' => ($icon = FilamentIcon::resolve('forms::components.file-upload.editor.actions.drag-move')) ? svg($icon, $iconSizeClasses)->toHtml() : new HtmlString('<svg class="' . $iconSizeClasses . '" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="currentColor" d="M13 6v5h5V7.75L22.25 12L18 16.25V13h-5v5h3.25L12 22.25L7.75 18H11v-5H6v3.25L1.75 12L6 7.75V11h5V6H7.75L12 1.75L16.25 6H13Z"/></svg>'),
                    'alpineClickHandler' => "editor.setDragMode('move')",
                ],
                [
                    'label' => __('filament-forms::components.file_upload.editor.actions.drag_crop.label'),
                    'iconHtml' => ($icon = FilamentIcon::resolve('forms::components.file-upload.editor.actions.drag-crop')) ? svg($icon, $iconSizeClasses)->toHtml() : new HtmlString('<svg class="' . $iconSizeClasses . '" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="currentColor" d="M17 23v-4H7q-.825 0-1.412-.587Q5 17.825 5 17V7H1V5h4V1h2v16h16v2h-4v4Zm0-8V7H9V5h8q.825 0 1.413.588Q19 6.175 19 7v8Z"/></svg>'),
                    'alpineClickHandler' => "editor.setDragMode('crop')",
                ],
                [
                    'label' => __('filament-forms::components.file_upload.editor.actions.zoom_in.label'),
                    'iconHtml' => svg(FilamentIcon::resolve('forms::components.file-upload.editor.actions.zoom-in') ?? 'heroicon-m-magnifying-glass-plus', $iconSizeClasses)->toHtml(),
                    'alpineClickHandler' => 'editor.zoom(0.1)',
                ],
                [
                    'label' => __('filament-forms::components.file_upload.editor.actions.zoom_out.label'),
                    'iconHtml' => svg(FilamentIcon::resolve('forms::components.file-upload.editor.actions.zoom-out') ?? 'heroicon-m-magnifying-glass-minus', $iconSizeClasses)->toHtml(),
                    'alpineClickHandler' => 'editor.zoom(-0.1)',
                ],
                [
                    'label' => __('filament-forms::components.file_upload.editor.actions.zoom_100.label'),
                    'iconHtml' => svg(FilamentIcon::resolve('forms::components.file-upload.editor.actions.zoom-100') ?? 'heroicon-m-arrows-pointing-out', $iconSizeClasses)->toHtml(),
                    'alpineClickHandler' => 'editor.zoomTo(1)',
                ],
            ],
            'move' => [
                [
                    'label' => __('filament-forms::components.file_upload.editor.actions.move_left.label'),
                    'iconHtml' => svg(FilamentIcon::resolve('forms::components.file-upload.editor.actions.move-left') ?? 'heroicon-m-arrow-left-circle', $iconSizeClasses)->toHtml(),
                    'alpineClickHandler' => 'editor.move(-10, 0)',
                ],
                [
                    'label' => __('filament-forms::components.file_upload.editor.actions.move_right.label'),
                    'iconHtml' => svg(FilamentIcon::resolve('forms::components.file-upload.editor.actions.move-right') ?? 'heroicon-m-arrow-right-circle', $iconSizeClasses)->toHtml(),
                    'alpineClickHandler' => 'editor.move(10, 0)',
                ],
                [
                    'label' => __('filament-forms::components.file_upload.editor.actions.move_up.label'),
                    'iconHtml' => svg(FilamentIcon::resolve('forms::components.file-upload.editor.actions.move-up') ?? 'heroicon-m-arrow-up-circle', $iconSizeClasses)->toHtml(),
                    'alpineClickHandler' => 'editor.move(0, -10)',
                ],
                [
                    'label' => __('filament-forms::components.file_upload.editor.actions.move_down.label'),
                    'iconHtml' => svg(FilamentIcon::resolve('forms::components.file-upload.editor.actions.move-down') ?? 'heroicon-m-arrow-down-circle', $iconSizeClasses)->toHtml(),
                    'alpineClickHandler' => 'editor.move(0, 10)',
                ],
            ],
            'transform' => [
                [
                    'label' => __('filament-forms::components.file_upload.editor.actions.rotate_left.label'),
                    'iconHtml' => svg(FilamentIcon::resolve('forms::components.file-upload.editor.actions.rotate-left') ?? 'heroicon-m-arrow-uturn-left', $iconSizeClasses)->toHtml(),
                    'alpineClickHandler' => 'editor.rotate(-90)',
                ],
                [
                    'label' => __('filament-forms::components.file_upload.editor.actions.rotate_right.label'),
                    'iconHtml' => svg(FilamentIcon::resolve('forms::components.file-upload.editor.actions.rotate-right') ?? 'heroicon-m-arrow-uturn-right', $iconSizeClasses)->toHtml(),
                    'alpineClickHandler' => 'editor.rotate(90)',
                ],
                [
                    'label' => __('filament-forms::components.file_upload.editor.actions.flip_horizontal.label'),
                    'iconHtml' => ($icon = FilamentIcon::resolve('forms::components.file-upload.editor.actions.flip-horizontal')) ? svg($icon, $iconSizeClasses)->toHtml() : new HtmlString('<svg class="' . $iconSizeClasses . '" viewBox="0 0 24 24"><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m3 7l5 5l-5 5V7m18 0l-5 5l5 5V7m-9 13v2m0-8v2m0-8v2m0-8v2"/></svg>'),
                    'alpineClickHandler' => 'editor.scaleX(-editor.getData().scaleX || -1)',
                ],
                [
                    'label' => __('filament-forms::components.file_upload.editor.actions.flip_vertical.label'),
                    'iconHtml' => ($icon = FilamentIcon::resolve('forms::components.file-upload.editor.actions.flip-vertical')) ? svg($icon, $iconSizeClasses)->toHtml() : new HtmlString('<svg class="' . $iconSizeClasses . '" viewBox="0 0 24 24"><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m17 3l-5 5l-5-5h10m0 18l-5-5l-5 5h10M4 12H2m8 0H8m8 0h-2m8 0h-2"/></svg>'),
                    'alpineClickHandler' => 'editor.scaleY(-editor.getData().scaleY || -1)',
                ],
            ],
        ];
    }


    // dah belh

    public function getMimeTypeMap()
    {
        logger($this->mimeTypeMap);
        return $this->evaluate($this->mimeTypeMap);
    }

    public function mimeTypeMap(array | Closure $map): static
    {
        $this->mimeTypeMap = $map;
        return $this;
    }
}
