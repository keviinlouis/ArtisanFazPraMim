<?php
namespace DumpNamespace;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\MorphTo;


/**
 * App\Entities\File
 *
 * @property int $id
 * @property string $name
 * @property string|null $extension
 * @property string $path
 * @property string|null $url
 * @property string|null $type
 * @property string|null $description
 * @property string $model_type
 * @property int $model_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $name_thumb
 * @property-read string $name_with_extension
 * @property-read mixed $path_thumb
 * @property-read mixed $path_without_name
 * @property-read mixed $url_thumb
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $model
 * @method static \Illuminate\Database\Eloquent\Builder|File whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|File whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|File whereExtension($value)
 * @method static \Illuminate\Database\Eloquent\Builder|File whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|File whereModelId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|File whereModelType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|File whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|File wherePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder|File whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|File whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|File whereUrl($value)
 * @mixin \Eloquent
 */
class File extends BaseModel
{
    const TYPES_IMAGE = ['jpg', 'jpeg', 'png', 'gif', 'bmp'];
    const THUMB_PREFIX = 'thumb_';
    const SIZE_SMALL = 'S';
    const SIZE_ORIGINAL = 'O';
    const THUMB_WIDTH = 256;
    const THUMB_HEIGHT = 256;

    public static $snakeAttributes = false;


    protected $casts = [
        'type' => 'int',
        'model_id' => 'int'
    ];
    protected $fillable = [
        'name',
        'extension',
        'type',
        'path',
        'url',
        'description',
        'model_id',
        'model_type'
    ];

    /**
     * The morph relation
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function model(): MorphTo
    {
        return $this->morphTo('model');
    }

    /**
     * Mutator to get url of the thumbnail
     * @return string
     */
    public function getUrlThumbAttribute(): string
    {
        $url = explode('/', $this->url);
        $thumb = self::THUMB_PREFIX.end($url);
        $url[count($url)-1] = $thumb;
        $urlThumb = implode('/', $url);
        return $urlThumb;
    }

    /**
     * Mutator to get the path of the thumbnail
     * @return string
     */
    public function getPathThumbAttribute(): string
    {
        $path = explode('/', $this->path);
        $thumb = self::THUMB_PREFIX.end($path);
        $path[count($path)-1] = $thumb;
        $pathThumb = implode('/', $path);
        return $pathThumb;
    }
    /**
     * Mutator to get the name with the extension
     * @return string
     */
    public function getNameWithExtensionAttribute()
    {
        $extension = str_replace('.', '', $this->extension);
        return $this->name.'.'.$extension;
    }

    /**
     * Mutator to get the path without the name
     * @return string
     */
    public function getPathWithoutNameAttribute(): string
    {
        return str_replace($this->name, '', $this->path);
    }

    /**
     * Mutator to get the name of the thumbnail
     * @return string
     */
    public function getNameThumbAttribute(): string
    {
        return self::THUMB_PREFIX.$this->name;
    }

    /**
     * Check if the file exists
     * @return bool
     */
    public function fileExists(): bool
    {
        return \Storage::exists($this->path);
    }

    /**
     * Check if has Thumbnail
     * @return bool
     */
    public function hasThumb(): bool
    {
        return \Storage::exists($this->path_thumb);
    }

    /**
     * Delete the file by path
     * @return bool
     */
    public function removeFile(): bool
    {
        return \Storage::delete($this->path);
    }

    /**
     * Delete the thumbnail by path
     * @return bool
     */
    public function removeThumb(): bool
    {
        return $this->hasThumb() && \Storage::delete($this->path_thumb);
    }
    /**
     * Move the file to passed path
     * @param $path
     * @return $this
     * @throws \Exception
     */
    public function moveTo($path): self
    {
        $this->checkDestinationPathExistsAndCreate($path);

        \Storage::move($this->path, $path.DIRECTORY_SEPARATOR.$this->name_with_extension);

        return $this;
    }
    /**
     * Copy file to passed path
     * @param $path
     * @return $this
     * @throws \Exception
     */
    public function copyTo($path): self
    {
        $this->checkDestinationPathExistsAndCreate($path);

        \Storage::copy($this->path, $path.DIRECTORY_SEPARATOR.$this->name_with_extension);

        return $this;
    }
    /**
     * Check if destination path exists and create if not exists
     * @param $path
     * @throws \Exception
     */
    public function checkDestinationPathExistsAndCreate($path)
    {
        if(!\Storage::exists($path)){
            \Storage::makeDirectory($path);
        }
    }

    /**
     * Check if is a image
     * @return bool
     */
    public function isImage()
    {
        return in_array($this->extension, self::TYPES_IMAGE) !== false;
    }
}
