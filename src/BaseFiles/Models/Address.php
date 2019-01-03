<?php

namespace DumpNamespace;

use App\Models\BaseModel as Eloquent;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Jcf\Geocode\Exceptions\EmptyArgumentsException;
use Jcf\Geocode\Geocode;


/**
 * App\Models\Address
 *
 * @property int $id
 * @property string $zip_code
 * @property string $street
 * @property int $number
 * @property string|null $complement
 * @property string|null $neighborhood
 * @property string $city
 * @property string $state
 * @property string|null $country
 * @property float|null $latitude
 * @property float|null $longitude
 * @property string $model_type
 * @property int $model_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read string $full_address_display
 * @property-read mixed $zip_code_display
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $model
 * @method static \Illuminate\Database\Eloquent\Builder|Address whereCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Address whereComplement($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Address whereCountry($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Address whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Address whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Address whereLatitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Address whereLongitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Address whereModelId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Address whereModelType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Address whereNeighborhood($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Address whereNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Address whereState($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Address whereStreet($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Address whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Address whereZipCode($value)
 * @mixin \Eloquent
 */
class Address extends Eloquent
{
    public static $snakeAttributes = false;
    protected $casts = [
        'number' => 'int',
        'modele_id' => 'int',
        'latitude' => 'float',
        'longitude' => 'float',
    ];
    protected $fillable = [
        'zip_code',
        'street',
        'number',
        'complement',
        'neighborhood',
        'city',
        'state',
        'country',
        'latitude',
        'longitude',
        'model_id',
        'model_type',
    ];

    /**
     * The morph relation
     * @return MorphTo
     */
    public function model(): MorphTo
    {
        return $this->morphTo('model');
    }

    /**
     * Get the zip code with mask
     * @return string
     */
    public function getZipCodeDisplayAttribute(): string
    {
        return $this->makeMaskZipCode($this->zip_code);
    }

    /**
     * Get the full address string
     * @return string
     */
    public function getFullAddressDisplayAttribute(): string
    {
        $endereco = $this->street . ', ' . $this->number . ', ';
        if ($this->neighborhood) {
            $endereco .= $this->neighborhood . ', ';
        }
        $endereco .= $this->city . ', ' . $this->state;
        return $endereco;
    }

    /**
     * Check if has minimum of address to exists
     * @return bool
     */
    public function hasDadosAddress(): bool
    {
        return $this->street && $this->number && $this->city && $this->state;
    }

    /**
     * Check if has Latitude and Longitude
     * @return bool
     */
    public function hasLatLong(): bool
    {
        return $this->latitude && $this->longitude;
    }

    /**
     * Load the latitude and longitude by the full address
     * @return Address
     */
    public function loadLatLong(): self
    {
        try {
            $response = Geocode::make()->address($this->full_address_display);
            if ($response) {
                $this->latitude = $response->latitude();
                $this->longitude = $response->longitude();

                $this->save();
            }
        } catch (EmptyArgumentsException $e) {
            \Log::channel('geocode')->error(
                'Can\' find latlong by address',
                ['full_address' => $this->full_address_display]
            );
        }
        return $this;
    }

    /**
     * Load the full address by the latitude and longitude
     * @return Address
     */
    public function loadAddressByLatLong(): self
    {
        try {
            $endereco = Geocode::make()->latLng($this->latitude, $this->longitude);
            if (!$endereco) {
                throw new \Exception();
            }
            $data = self::getDataAddressByGeocodeResponse($endereco->response);

            $data['latitude'] = $this->latitude;
            $data['longitude'] = $this->longitude;

            $this->fill($data);

            $this->save();
        } catch (\Exception $e) {
            \Log::channel('geocode')->error(
                'Can\' find address by latlong',
                [
                    'latitude' => $this->latitude,
                    'longitude' => $this->longitude,
                ]
            );
        }

        return $this;
    }

    /**
     * Build a full Address model by the data response
     *
     * @param $response
     *
     * @return array
     */
    static public function getDataAddressByGeocodeResponse($response): array
    {
        $data = [
            'zip_code' => self::findFieldInGeocodeResponse($response, 'zip_code'),
            'street' => self::findFieldInGeocodeResponse($response, 'street', true),
            'number' => self::findFieldInGeocodeResponse($response, 'number'),
            'neighborhood' => self::findFieldInGeocodeResponse($response, 'neighborhood', true),
            'city' => self::findFieldInGeocodeResponse($response, 'city', true),
            'state' => self::findFieldInGeocodeResponse($response, 'state', false),
            'country' => self::findFieldInGeocodeResponse($response, 'country', false),
        ];
        return $data;
    }

    /**
     * Find the field passed by geocode response
     * @param $response
     * @param $field
     * @param bool $longName
     *
     * @return null|string
     */
    static public function findFieldInGeocodeResponse($response, $field, $longName = false): ?string
    {
        foreach ($response->address_components as $component) {
            foreach ($component->types as $type) {
                if ($type == self::guessField($field)) {
                    return $longName ? $component->long_name : $component->short_name;
                }
            }
        }
        return null;
    }

    /**
     * Guess the field passed by function to google field response
     * @param $field
     *
     * @return null|string
     */
    static public function guessField($field): ?string
    {
        switch ($field) {
            case 'rua':
            case 'street':
            case 'route':
                return 'route';
            case 'street_number':
            case 'numero':
            case 'number':
                return 'street_number';
            case 'cidade':
            case 'city':
            case 'administrative_area_level_2':
                return 'administrative_area_level_2';
            case 'estado':
            case 'uf':
            case 'state':
            case 'administrative_area_level_1':
                return 'administrative_area_level_1';
            case 'bairro':
            case 'neighborhood':
            case 'sublocality_level_1':
                return 'sublocality_level_1';
            case 'postal_code':
            case 'cep':
            case 'zip_code':
            case 'zipecode':
                return 'postal_code';
            case 'country':
            case 'pais':
                return 'country';
            default:
                return null;
        }
    }
}
