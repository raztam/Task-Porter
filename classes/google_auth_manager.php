<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Google auth manager class for TaskPorter.
 *
 * @package    local_taskporter
 * @copyright  2025 Your Name <your.email@example.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_taskporter\google;

defined('MOODLE_INTERNAL') || die();
require_once(dirname(dirname(__FILE__)) . '/config.php');


/**
 * Class responsible for managing Google API OAuth credentials
 */
class google_auth_manager {
    /** @var \Google_Client The Google API client */
    protected $client;

    /** @var int The user ID */
    protected $userid;

    /**
     * Constructor.
     *
     * @param int $userid User ID
     */
    public function __construct(int $userid = 0) {
        global $USER;
        $this->userid = $userid ?: $USER->id;
        $this->initialize_client();
    }

    /**
     * Initialize the Google client with stored credentials.
     */
    protected function initialize_client() {
        global $CFG, $googleapiconfig;
        require_once($CFG->dirroot . '/local/taskporter/vendor/autoload.php');

        // Create Google client.
        $this->client = new \Google_Client();
        $this->client->setApplicationName('Moodle TaskPorter');
        $this->client->setScopes([\Google_Service_Calendar::CALENDAR]);

        // Get client credentials from plugin settings.
        $clientid = $googleapiconfig['google_client_id'];
        $clientsecret = $googleapiconfig['google_client_secret'];
        $redirecturi = (new \moodle_url('/local/taskporter/google_oauth_callback.php'))->out(false);

        $this->client->setClientId($clientid);
        $this->client->setClientSecret($clientsecret);
        $this->client->setRedirectUri( $redirecturi);
        $this->client->setAccessType('offline');  // We need refresh token.
        $this->client->setPrompt('consent');      // Force to approve the access each time.

        // Try to load saved token.
        $token = $this->get_stored_token();
        if ($token) {
            $this->client->setAccessToken($token);

            // If token is expired, try to refresh it.
            if ($this->client->isAccessTokenExpired()) {
                $this->refresh_token();
            }
        }
    }

    /**
     * Get authorization URL for OAuth2 flow.
     *
     * @return string The authorization URL
     */
    public function get_auth_url($state=[]) {
        if (!empty($state)) {
            $this->client->setState(json_encode($state));
        }
        return $this->client->createAuthUrl();
    }

    /**
     * Get stored token for current user.
     *
     * @return array|null The token array or null if not found
     */
    public function get_stored_token() {
        global $DB;

        $record = $DB->get_record('local_taskporter_user_tokens', [
            'userid' => $this->userid,
            'provider' => 'google',
        ]);

        if ($record && !empty($record->token_data)) {
            return json_decode($record->token_data, true);
        }

        return null;
    }

    /**
     * Store new token for current user.
     *
     * @param string $code Authorization code from Google
     * @return bool True if successful
     */
    public function store_token_from_code($code) {
        try {
            $token = $this->client->fetchAccessTokenWithAuthCode($code);
            if (isset($token['error'])) {
                return false;
            }

            $this->client->setAccessToken($token);
            return $this->save_token($token);
        } catch (\Exception $e) {
            debugging('Error storing Google token: ' . $e->getMessage(), DEBUG_DEVELOPER);
            return false;
        }
    }

    /**
     * Refresh the access token.
     *
     * @return bool True if successful
     */
    public function refresh_token() {
        try {
            if (!$this->client->getRefreshToken()) {
                return false;
            }

            $token = $this->client->fetchAccessTokenWithRefreshToken();
            if (isset($token['error'])) {
                return false;
            }

            return $this->save_token($token);
        } catch (\Exception $e) {
            debugging('Error refreshing Google token: ' . $e->getMessage(), DEBUG_DEVELOPER);
            return false;
        }
    }

    /**
     * Save token to database.
     *
     * @param array $token The token array
     * @return bool True if successful
     */
    protected function save_token($token) {
        global $DB;

        try {
            $record = $DB->get_record('local_taskporter_user_tokens', [
                'userid' => $this->userid,
                'provider' => 'google',
            ]);

            if ($record) {
                // When refreshing tokens, Google typically doesn't send a new refresh token
                // We need to preserve the existing one if a new one isn't included
                if (empty($token['refresh_token'])) {
                    $existingToken = json_decode($record->token_data, true);
                    if (!empty($existingToken['refresh_token'])) {
                        $token['refresh_token'] = $existingToken['refresh_token'];
                    }
                }

                $record->token_data = json_encode($token);
                $record->timemodified = time();
                return $DB->update_record('local_taskporter_user_tokens', $record);
            } else {
                // First time authentication - store everything
                $record = new \stdClass();
                $record->userid = $this->userid;
                $record->token_data = json_encode($token);
                $record->provider = 'google';
                $record->timecreated = time();
                $record->timemodified = time();
                return $DB->insert_record('local_taskporter_user_tokens', $record) > 0;
            }
        } catch (\Exception $e) {
            debugging('Error saving Google token: ' . $e->getMessage(), DEBUG_DEVELOPER);
            return false;
        }
    }

    /**
     * Revoke the user's token and remove from database.
     *
     * @return bool True if successful
     */
    public function revoke_token() {
        global $DB;

        try {
            $token = $this->get_stored_token();
            if ($token && isset($token['access_token'])) {
                $this->client->revokeToken($token['access_token']);
            }

            // Remove from database regardless of revocation success
            return $DB->delete_records('local_taskporter_user_tokens', ['userid' => $this->userid]);
        } catch (\Exception $e) {
            debugging('Error revoking Google token: ' . $e->getMessage(), DEBUG_DEVELOPER);
            return false;
        }
    }

    /**
     * Check if user is authenticated with Google.
     *
     * @return bool True if authenticated
     */
    public function is_authenticated() {
        $token = $this->get_stored_token();
        if (!$token) {
            return false;
        }

        if ($this->client->isAccessTokenExpired()) {
            return $this->refresh_token();
        }

        return true;
    }

    /**
     * Get the Google client instance.
     *
     * @return \Google_Client The configured client
     */
    public function get_client() {
        return $this->client;
    }

    /**
    * Get the redirect URI that's configured for OAuth
    *
    * @return string The configured redirect URI
    */
    public function get_redirect_uri() {
        return $this->client->getRedirectUri();
    }

/**
 * Handle OAuth callback and exchange code for access token.
 *
 * @param string $code Authorization code from Google
 * @return bool True if successful
 */
public function handle_callback($code) {
    return $this->store_token_from_code($code);
}

}
