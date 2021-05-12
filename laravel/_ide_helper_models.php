<?php

// @formatter:off
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */


namespace App\Models{
/**
 * App\Models\Agent
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Agent newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Agent newQuery()
 * @method static \Illuminate\Database\Query\Builder|Agent onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Agent query()
 * @method static \Illuminate\Database\Query\Builder|Agent withTrashed()
 * @method static \Illuminate\Database\Query\Builder|Agent withoutTrashed()
 * @mixin \Eloquent
 */
	class IdeHelperAgent extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\IpInfo
 *
 * @mixin IdeHelperIpInfo
 * @property int $id
 * @property mixed $ipnum
 * @property int $mask
 * @property mixed $start
 * @property mixed $end
 * @property string|null $inetnum
 * @property string|null $netname
 * @property string|null $country
 * @property string|null $orgname
 * @property string|null $geoipcountry
 * @property string|null $last_check
 * @property int $checked
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|IpInfo newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|IpInfo newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|IpInfo query()
 * @method static \Illuminate\Database\Eloquent\Builder|IpInfo whereChecked($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IpInfo whereCountry($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IpInfo whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IpInfo whereEnd($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IpInfo whereGeoipcountry($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IpInfo whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IpInfo whereInetnum($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IpInfo whereIpnum($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IpInfo whereLastCheck($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IpInfo whereMask($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IpInfo whereNetname($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IpInfo whereOrgname($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IpInfo whereStart($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IpInfo whereUpdatedAt($value)
 */
	class IdeHelperIpInfo extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\User
 *
 * @mixin IdeHelperUser
 * @property int $id
 * @property string $name
 * @property string $email
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $two_factor_secret
 * @property string|null $two_factor_recovery_codes
 * @property string|null $remember_token
 * @property int|null $current_team_id
 * @property string|null $profile_photo_path
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read string $profile_photo_url
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection|\Illuminate\Notifications\DatabaseNotification[] $notifications
 * @property-read int|null $notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\Laravel\Sanctum\PersonalAccessToken[] $tokens
 * @property-read int|null $tokens_count
 * @method static \Database\Factories\UserFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|User query()
 * @method static \Illuminate\Database\Eloquent\Builder|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereCurrentTeamId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereProfilePhotoPath($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereTwoFactorRecoveryCodes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereTwoFactorSecret($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereUpdatedAt($value)
 */
	class IdeHelperUser extends \Eloquent implements \Illuminate\Contracts\Auth\MustVerifyEmail {}
}

