<?php
if (!defined('ABSPATH')) exit;

class StatPal_Settings {

    public function __construct(){
        add_action('admin_menu', [$this, 'add_menu']);
        add_action('admin_init', [$this, 'register_settings']);
    }

    public function add_menu(){
        add_menu_page(
            'Live Sports',
            'Live Sports',
            'manage_options',
            'live-sports',
            [$this, 'render_page'],
            'dashicons-admin-generic',
            6
        );
    }

    public function register_settings(){
        register_setting('statpal_settings_group', 'statpal_api_key', [
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => ''
        ]);

        register_setting('statpal_settings_group', 'statpal_active_sports', [
            'type' => 'string',
            'default' => wp_json_encode(['sports' => ['nfl' => ['enabled' => true], 'mlb' => ['enabled' => true]], 'default_sport' => 'nfl'])
        ]);

        register_setting('statpal_settings_group', 'statpal_max_games', [
            'type' => 'integer',
            'sanitize_callback' => 'absint',
            'default' => 10
        ]);
    }

    private function get_validated_sports_data() {
        $raw_sports = get_option('statpal_active_sports', '');
        
        if (empty($raw_sports)) {
            $def = ['sports' => ['nfl' => ['enabled' => true], 'mlb' => ['enabled' => true]], 'default_sport' => 'nfl'];
            return wp_json_encode($def);
        }

        $decoded = json_decode($raw_sports, true);
        // Migration from legacy comma separated strings
        if (!$decoded && is_string($raw_sports) && strpos($raw_sports, '{') === false) { 
            $parts = array_filter(array_map('trim', explode(',', $raw_sports)));
            $json = ['sports' => [], 'default_sport' => $parts[0] ?? 'nfl'];
            foreach($parts as $p) {
                $json['sports'][strtolower($p)] = ['enabled' => true];
            }
            $migrated = wp_json_encode($json);
            update_option('statpal_active_sports', $migrated);
            return $migrated;
        }

        return $raw_sports;
    }

    public function render_page(){
        wp_enqueue_style('statpal-admin-css', plugins_url('../assets/admin-style.css', __FILE__));
        wp_enqueue_script('statpal-admin-js', plugins_url('../assets/admin-settings.js', __FILE__), [], '1.0.0', true);

        $api_key = get_option('statpal_api_key', '');
        $max_games = get_option('statpal_max_games', 10);
        $sports_data_json = $this->get_validated_sports_data();
        ?>
        <div class="statpal-admin-wrap">
            <div class="statpal-admin-header">
                <h1>StatPal Sports Dashboard Configuration</h1>
                <p>Manage your API connection, control what sports are visible, and set maximum data limitations.</p>
            </div>

            <form method="post" action="options.php">
                <?php settings_fields('statpal_settings_group'); ?>
                
                <div class="statpal-card">
                    <h2>Authentication</h2>
                    <div class="statpal-form-group">
                        <label for="statpal_api_key">StatPal API Key</label>
                        <input type="text" id="statpal_api_key" name="statpal_api_key" value="<?php echo esc_attr($api_key); ?>" />
                        <div class="description">Required to fetch live scores, odds, schedules, and rosters from StatPal servers.</div>
                    </div>
                </div>

                <div class="statpal-card">
                    <h2>Game Limits (Global)</h2>
                    <div class="statpal-form-group">
                        <label for="statpal_max_games">Max Games Rendered</label>
                        <input type="number" id="statpal_max_games" name="statpal_max_games" value="<?php echo esc_attr($max_games); ?>" min="1" />
                        <div class="description">Define the maximum number of games/matches allowed to render per sport tab. A global limit helps prevent overly long pages and improves performance when an API payload contains dozens of matches.</div>
                    </div>
                </div>

                <div class="statpal-card">
                    <h2>Manage Active Sports</h2>
                    <p class="description" style="margin-bottom: 20px;">Use the input below to add new sports (e.g. <code>nba</code>, <code>f1</code>, <code>soccer</code>). You can selectively enable/disable specific sports for the dashboard. The sport selected as <strong>Default</strong> will be automatically expanded when the user opens the page.</p>
                    
                    <div class="statpal-add-sport-wrap">
                        <input type="text" id="statpal-new-sport" placeholder="Enter sport slug (e.g. nfl)" />
                        <button type="button" class="button button-secondary" id="statpal-btn-add">Add Sport</button>
                    </div>

                    <!-- Hidden JSON Configuration that gets posted -->
                    <input type="hidden" id="statpal_active_sports_data" name="statpal_active_sports" value="<?php echo esc_attr($sports_data_json); ?>" />
                    
                    <!-- Managed by Assets/admin-settings.js -->
                    <div id="statpal-sports-list" class="statpal-sports-list"></div>
                </div>

                <?php submit_button('Save Sports Settings', 'primary', 'submit', true, ['style' => 'font-size: 16px; padding: 6px 24px;']); ?>
            </form>
        </div>
        <?php
    }
}

new StatPal_Settings();
