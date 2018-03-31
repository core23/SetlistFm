<?php

declare(strict_types=1);

/*
 * (c) Christian Gripp <mail@core23.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Core23\SetlistFm\Model;

final class Geo
{
    /**
     * @var float
     */
    private $longitude;

    /**
     * @var float
     */
    private $latitude;

    /**
     * Geo constructor.
     *
     * @param float $longitude
     * @param float $latitude
     */
    public function __construct(float $longitude, float $latitude)
    {
        $this->longitude = $longitude;
        $this->latitude  = $latitude;
    }

    /**
     * @return float
     */
    public function getLongitude(): float
    {
        return $this->longitude;
    }

    /**
     * @return float
     */
    public function getLatitude(): float
    {
        return $this->latitude;
    }

    /**
     * @param array $data
     *
     * @return Geo
     */
    public static function fromApi(array $data): self
    {
        return new self(
            $data['long'],
            $data['lat']
        );
    }
}
