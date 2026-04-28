<?php
if (!defined('ABSPATH')) exit;

class StatPal_Render {

    public function __construct() {
        add_shortcode('statpal_dashboard', [$this, 'render_dashboard']);
    }

    private function get_team_options($slug) {
        $teams = [];
        if($slug === 'nfl') $teams = ['ari','atl','bal','buf','car','chi','cin','cle','dal','den','det','gb','hou','ind','jac','kc','mia','min','no','nyg','nyj','oak','phi','pit','sd','sea','sf','stl','tb','ten','wsh'];
        elseif($slug === 'nhl') $teams = ['ana','ari','bos','buf','car','cbj','cgy','chi','col','dal','det','edm','fla','la','min','mtl','nj','nsh','nyi','nyr','ott','phi','pit','sj','stl','tb','tor','van','wpg','wsh'];
        elseif($slug === 'mlb') $teams = ['ari','atl','bal','bos','chc','chw','cin','cle','col','det','hou','kc','laa','lad','mia','mil','min','nym','nyy','oak','phi','pit','sd','sea','sf','stl','tb','tex','tor','wsh'];
        elseif($slug === 'nba') $teams = ['atl','bkn','bos','cha','chi','cle','dal','den','det','gsw','hou','ind','lac','lal','mem','mia','mil','min','nop','nyk','okc','orl','phi','phx','por','sac','sas','tor','uta','was'];
        else $teams = ['ari','atl','bos','chi','dal','la','ny','phi','was'];
        $options = [];
        foreach($teams as $t) $options[$t] = strtoupper($t);
        return $options;
    }

    private function get_sports_config() {
        $raw_sports = get_option('statpal_active_sports', '');
        $decoded = json_decode($raw_sports, true);
        $slugs = [];
        $default_sport = '';

        if ($decoded && isset($decoded['sports'])) {
            $default_sport = $decoded['default_sport'] ?? '';
            foreach ($decoded['sports'] as $key => $conf) {
                if (!empty($conf['enabled'])) {
                    $slugs[] = $key;
                }
            }
        } else {
            // Legacy fallback if settings haven't been visited yet
            $slugs = array_filter(array_map('trim', explode(',', $raw_sports ?: 'nfl,mlb,nhl')));
            $default_sport = $slugs[0] ?? 'nfl';
        }

        
        $config = [];
        foreach($slugs as $slug) {
            $slug = strtolower($slug);
            $custom_name = !empty($decoded['sports'][$slug]['custom_name']) ? $decoded['sports'][$slug]['custom_name'] : strtoupper($slug);
            
            $logo_path = plugin_dir_path(__FILE__) . '../assets/images/' . $slug . '.png';
            $logo_url = file_exists($logo_path) ? plugins_url('../assets/images/' . $slug . '.png', __FILE__) : '';

            $tabs = [];
            if ($slug === 'horse-racing') {
                $tabs = [
                    'livescores' => [
                        'label' => 'Live Scores',
                        'endpoint' => '/api/v1/' . $slug . '/live/{country}',
                        'params' => [
                            'country' => [
                                'type' => 'select',
                                'label' => 'Select Country',
                                'options' => [
                                    'uk' => 'UK', 'usa' => 'USA', 'sa' => 'SA', 'france' => 'France'
                                ],
                                'default' => 'uk'
                            ]
                        ]
                    ],
                    'schedule' => [
                        'label' => 'Schedule',
                        'endpoint' => '/api/v1/' . $slug . '/schedule/{country}',
                        'params' => [
                            'country' => [
                                'type' => 'select',
                                'label' => 'Select Country',
                                'options' => [
                                    'uk' => 'UK', 'usa' => 'USA', 'sa' => 'SA', 'france' => 'France'
                                ],
                                'default' => 'uk'
                            ]
                        ]
                    ]
                ];
            } else {
                $tabs = [
                    'odds' => [ 'label' => 'Odds', 'endpoint' => '/api/v1/' . $slug . '/odds' ],
                    'livescores' => [ 'label' => 'Live Scores', 'endpoint' => '/api/v1/' . $slug . '/livescores' ],
                    'schedule' => [ 
                        'label' => 'Schedule', 
                        'endpoint' => '/api/v1/' . $slug . ($slug === 'f1' ? '/schedule' : '/season-schedule') 
                    ],
                    'standings' => [ 
                        'label' => 'Standings', 
                        'endpoint' => '/api/v1/' . $slug . ($slug === 'f1' ? '/team-standings' : '/standings') 
                    ],
                    'recent' => [
                        'label' => 'Recent / Upcoming',
                        'endpoint' => '/api/v1/' . $slug . '/daily/{day}',
                        'params' => [
                            'day' => [
                                'type' => 'select',
                                'label' => 'Select Day',
                                'options' => [
                                    'd-7' => 'd-7', 'd-6' => 'd-6', 'd-5' => 'd-5', 'd-4' => 'd-4', 'd-3' => 'd-3', 'd-2' => 'd-2', 'd-1' => 'd-1',
                                    'd1' => 'd1', 'd2' => 'd2', 'd3' => 'd3', 'd4' => 'd4', 'd5' => 'd5', 'd6' => 'd6', 'd7' => 'd7'
                                ],
                                'default' => 'd-1'
                            ]
                        ]
                    ],
                    'rosters' => [
                        'label' => 'Team Rosters',
                        'endpoint' => '/api/v1/' . $slug . '/rosters/{team}',
                        'params' => [
                            'team' => [
                                'type' => 'select',
                                'label' => 'Select Team',
                                'options' => $this->get_team_options($slug)
                            ]
                        ]
                    ],
                    'injuries' => [
                        'label' => 'Injuries',
                        'endpoint' => '/api/v1/' . $slug . '/injuries/{team}',
                        'params' => [
                            'team' => [
                                'type' => 'select',
                                'label' => 'Select Team',
                                'options' => $this->get_team_options($slug)
                            ]
                        ]
                    ],
                ];
            }

            $config[$slug] = [
                'id' => $slug,
                'name' => $custom_name,
                'icon' => 'dashicons-chart-bar', // default icon
                'logo_url' => $logo_url,
                'tabs' => $tabs
            ];
        }
        return ['sports' => $config, 'default' => $default_sport];
    }

    public function render_dashboard($atts) {
        wp_enqueue_script('statpal-script');
        wp_enqueue_style('statpal-style');

        $config_data = $this->get_sports_config();
        $sports = $config_data['sports'];
        $default_sport = $config_data['default'];

        $max_games = (int) get_option('statpal_max_games', 10);
        if ($max_games <= 0) $max_games = 10;

        ob_start();
        ?>
        <div class="statpal-dashboard-container" data-items-per-page="<?php echo esc_attr($max_games); ?>">
            <div class="statpal-sidebar">
                <div class="statpal-sidebar-title">Sports</div>
                <ul class="statpal-sport-list">
                    <?php 
                    $first_sport = true;
                    foreach ($sports as $id => $sport) : 
                        if ($default_sport && isset($sports[$default_sport])) {
                            $active = ($id === $default_sport) ? 'is-active' : '';
                        } else {
                            $active = $first_sport ? 'is-active' : '';
                            $first_sport = false;
                        }
                    ?>
                        <li class="statpal-sport-item <?php echo esc_attr($active); ?>" 
                            data-sport="<?php echo esc_attr($id); ?>">
                            <div class="statpal-sport-header">
                                <div class="statpal-sport-header-left">
                                    <?php if (!empty($sport['logo_url'])) : ?>
                                        <img src="<?php echo esc_url($sport['logo_url']); ?>" alt="<?php echo esc_attr($sport['name']); ?>" class="statpal-sport-header-img" />
                                    <?php else : ?>
                                        <span class="dashicons <?php echo esc_attr($sport['icon']); ?>"></span> 
                                    <?php endif; ?>
                                    <span class="statpal-sport-name"><?php echo esc_html($sport['name']); ?></span>
                                </div>
                                <span class="dashicons dashicons-arrow-down-alt2 statpal-arrow"></span>
                            </div>
                            <ul class="statpal-sport-tabs">
                                <?php foreach($sport['tabs'] as $tab_id => $tab_data): ?>
                                    <li class="statpal-tab-item <?php echo ($tab_id === 'odds' && $active) ? 'is-active' : ''; ?>" data-tab="<?php echo esc_attr($tab_id); ?>" data-endpoint="<?php echo esc_attr($tab_data['endpoint']); ?>" data-params="<?php echo esc_attr(wp_json_encode($tab_data['params'] ?? null)); ?>">
                                        <?php echo esc_html($tab_data['label']); ?>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            
            <div class="statpal-main-area">
                <div class="statpal-content-header">
                    <h2 class="statpal-content-title">
                        <span id="statpal-active-sport"></span> 
                        <span class="statpal-title-sep">></span> 
                        <span id="statpal-active-tab"></span>
                    </h2>
                    <div id="statpal-params-bar" class="statpal-params-bar"></div>
                </div>
                <div class="statpal-tabs-container" style="display:none;"></div>
                <div class="statpal-content-area" id="statpal-content-area">
                     <div class="statpal-loading-overlay">
                         <div class="statpal-spinner"></div>
                     </div>
                     <div class="statpal-content-results" id="statpal-content-results"></div>
                     <div class="statpal-pagination-controls" style="display:none;">
                         <button class="statpal-btn-page statpal-prev-page">Previous</button>
                         <span class="statpal-page-info" style="align-self:center;">Page <span class="current-page">1</span></span>
                         <button class="statpal-btn-page statpal-next-page">Next</button>
                     </div>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    public function render_api_data($data, $tab) {
        if (empty($data) || is_wp_error($data)) {
            return '<div class="statpal-empty">No Data Available</div>';
        }

        ob_start();

        if ($this->is_f1($data)) {
            $this->render_f1_view($data, $tab);
            return ob_get_clean();
        }

        if ($this->is_horse_racing($data)) {
            $this->render_horse_racing_view($data, $tab);
            return ob_get_clean();
        }

        $matches = $this->extract_matches($data, $tab);

        if ($tab === 'livescores' && !empty($matches)) {
            $this->render_livescores_view($matches, $data);
            return ob_get_clean();
        }
        
        if ($tab === 'odds' && !empty($matches)) {
            $structured_odds = $this->prepare_structured_odds($matches);
            $this->render_odds_view($matches, $structured_odds);
            return ob_get_clean();
        }

        if (in_array($tab, ['schedule', 'recent'])) {
            $items = $this->extract_generic_list($data, $tab);
            if (!empty($items)) {
                $this->render_match_view_by_tab($items, $tab, $data);
                return ob_get_clean();
            }
        }
        
        if ($tab === 'standings') {
            $items = $this->extract_generic_list($data, $tab);
            if (!empty($items)) {
                $this->render_standings_view($items, $data);
                return ob_get_clean();
            }
        }
        
        if (in_array($tab, ['rosters', 'teams', 'injuries'])) {
            $items = $this->extract_generic_list($data, $tab);
            if (!empty($items)) {
                $this->render_players_view($items, $tab, $data);
                return ob_get_clean();
            }
        }

        // Unknown or unimplemented tab fallback
        echo '<div class="statpal-empty">No Data Available</div>';

        return ob_get_clean();
    }

    private function extract_matches($data, $tab) {
        if (isset($data['livescores']['tournament'])) {
            $tournaments = $data['livescores']['tournament'];
            if(isset($tournaments['match'])) return $this->normalize_list($tournaments['match']);
            if(isset($tournaments[0]['match'])) {
                $merged = [];
                foreach($tournaments as $t) {
                    if(isset($t['match'])) {
                        $merged = array_merge($merged, $this->normalize_list($t['match']));
                    }
                }
                return $merged;
            }
        }
        if (isset($data['odds']['category']['matches']['match'])) {
            return $this->normalize_list($data['odds']['category']['matches']['match']);
        }
        if (isset($data['matches'])) {
            return $this->normalize_list($data['matches']);
        }
        if (isset($data['teams'])) {
            return $this->normalize_list($data['teams']);
        }
        if (is_array($data) && isset($data[0])) {
            return $data;
        }

        foreach (['live_matches','results','finished_matches'] as $root_key){
            if(isset($data[$root_key]['league'])){
                $leagues = $data[$root_key]['league'];
                if(isset($leagues['match'])) return $this->normalize_list($leagues['match']);
                if(isset($leagues[0]['match'])) {
                    $merged = [];
                    foreach($leagues as $l) {
                        if(isset($l['match'])) {
                            $merged = array_merge($merged, $this->normalize_list($l['match']));
                        }
                    }
                    return $merged;
                }
            }
        }

        return [$data];
    }

    private function extract_generic_list($data, $tab = '') {
        $targets = [
            'rosters' => 'player',
            'injuries' => 'player',
            'standings' => 'team',
            'schedule' => 'match',
            'recent' => 'match',
            'livescores' => 'match'
        ];
        
        $target = $targets[$tab] ?? '';
        if ($target) {
            $items = $this->collect_deep_items($data, $target);
            if (!empty($items)) return $items;
        }
        
        // Fallback targets
        foreach(['match', 'player', 'team', 'item', 'driver', 'race'] as $t) {
            $items = $this->collect_deep_items($data, $t);
            if (!empty($items)) return $items;
        }

        // Generic fallback
        if(is_array($data) && array_keys($data) === range(0, count($data) - 1)) return $data;
        if(is_array($data)) {
            foreach($data as $val) {
                if(is_array($val) && array_keys($val) === range(0, count($val) - 1)) return $val;
            }
        }
        return is_array($data) ? [$data] : [];
    }

    private function collect_deep_items($data, $target_key, $parent_name = '') {
        $found = [];
        if (!is_array($data)) return $found;
        
        $current_name = $data['name'] ?? $parent_name;

        foreach ($data as $k => $v) {
            if ($k === $target_key && is_array($v)) {
                $normals = $this->normalize_list($v);
                foreach($normals as $n) {
                    if (is_array($n) && $current_name && !isset($n['_group'])) {
                        $n = array_merge(['_group' => $current_name], $n);
                    }
                    $found[] = $n;
                }
            } elseif (is_array($v)) {
                $found = array_merge($found, $this->collect_deep_items($v, $target_key, $current_name));
            }
        }
        return $found;
    }

    private function normalize_list($item) {
        if(is_array($item) && array_keys($item) === range(0, count($item) - 1)) return $item;
        return [$item];
    }
    
    private function render_livescores_view($matches, $data = []) {
        if (!file_exists(plugin_dir_path(__FILE__) . 'template-livescores.php')) {
            echo '<div class="statpal-empty">Template Missing</div>';
            return;
        }
        include plugin_dir_path(__FILE__) . 'template-livescores.php';
    }
    
    private function render_odds_view($matches, $structured_odds = []) {
        if (!file_exists(plugin_dir_path(__FILE__) . 'template-odds.php')) {
            echo '<div class="statpal-empty">Template Missing</div>';
            return;
        }
        include plugin_dir_path(__FILE__) . 'template-odds.php';
    }

    private function is_f1($data) {
        if (!is_array($data)) return false;
        if (isset($data['livescore']['sport']) && strtolower($data['livescore']['sport']) === 'formula 1') return true;
        if (isset($data['livescores']['sport']) && strtolower($data['livescores']['sport']) === 'formula 1') return true;
        if (isset($data['fixtures']['sport']) && strtolower($data['fixtures']['sport']) === 'formula 1') return true;
        if (isset($data['standings']['sport']) && strtolower($data['standings']['sport']) === 'formula 1') return true;
        return false;
    }

    private function render_f1_view($data, $tab) {
        $template = plugin_dir_path(__FILE__) . 'template-f1-' . $tab . '.php';
        if (file_exists($template)) {
            include $template;
        } else {
            echo '<div class="statpal-empty">F1 Template Missing: ' . esc_html($tab) . '</div>';
        }
    }

    private function is_horse_racing($data) {
        if (!is_array($data)) return false;
        if (isset($data['scores']['sport']) && strtolower($data['scores']['sport']) === 'horse racing') return true;
        return false;
    }

    private function render_horse_racing_view($data, $tab) {
        $template = plugin_dir_path(__FILE__) . 'template-horse-racing-' . $tab . '.php';
        if (file_exists($template)) {
            include $template;
        } else {
            echo '<div class="statpal-empty">Horse Racing Template Missing: ' . esc_html($tab) . '</div>';
        }
    }

    private function render_match_view_by_tab($items, $tab, $data = []) {
        $template = plugin_dir_path(__FILE__) . 'template-' . $tab . '.php'; // 'schedule' or 'recent'
        if (!file_exists($template)) {
            echo '<div class="statpal-empty">Template Missing</div>';
            return;
        }
        include $template;
    }

    private function render_standings_view($items, $data = []) {
        if (!file_exists(plugin_dir_path(__FILE__) . 'template-standings.php')) {
            echo '<div class="statpal-empty">Template Missing</div>';
            return;
        }

        // Target-specific logo fallback
        $root = $data['standings'] ?? $data;
        $team_info = $root['team'] ?? $root['constructor'] ?? $root;
        $team_logo = $this->get_team_logo($team_info);
        if (empty($team_logo) && $root !== $team_info) {
             $team_logo = $this->get_team_logo($root);
        }

        include plugin_dir_path(__FILE__) . 'template-standings.php';
    }

    private function render_players_view($items, $tab, $data = []) {
        // $tab can be 'rosters', 'teams', 'injuries'
        $name = ($tab === 'injuries') ? 'injuries' : 'rosters';
        $template = plugin_dir_path(__FILE__) . 'template-' . $name . '.php';
        if (!file_exists($template)) {
            echo '<div class="statpal-empty">Template Missing</div>';
            return;
        }

        // Target-specific logo fallback (NFL case: image sitting at root)
        $root = $data[$tab] ?? $data;
        $team_info = $root['team'] ?? $root['constructor'] ?? $root['category'] ?? $root;
        $team_logo = $this->get_team_logo($team_info);
        if (empty($team_logo) && $root !== $team_info) {
             $team_logo = $this->get_team_logo($root);
        }

        include $template;
    }

    private function extract_statpal_odds($odds) {
        if(empty($odds) || !is_array($odds) || empty($odds['type'])) return 'N/A';
        $types = isset($odds['type'][0]) ? $odds['type'] : [$odds['type']];
        $type = $types[0] ?? null;
        if(!$type || empty($type['bookmaker'])) return 'N/A';
        
        $bms = isset($type['bookmaker'][0]) ? $type['bookmaker'] : [$type['bookmaker']];
        $bm = $bms[0] ?? null;
        if(empty($bm['odd'])) return 'N/A';

        $parts = [];
        $odds_list = isset($bm['odd'][0]) ? $bm['odd'] : [$bm['odd']];
        foreach($odds_list as $odd){
            $n = $odd['name'] ?? '';
            $v = $odd['value'] ?? '';
            $parts[] = '<span class="statpal-odd-pill">' . esc_html($n) . ' <b>' . esc_html($v) . '</b></span>';
        }
        return implode(' ', array_filter($parts));
    }

    private function prepare_structured_odds($matches) {
        $all_types = [];
        $all_bookmakers = [];
        $structured_matches = [];
       
        foreach ($matches as $match) {
            $odds_data = $match['odds'] ?? null;
            if (empty($odds_data) || !is_array($odds_data) || empty($odds_data['type'])) continue;

            $match_odds = [];
            $types = isset($odds_data['type'][0]) ? $odds_data['type'] : [$odds_data['type']];
           
            foreach ($types as $type) {
                $type_name = $type['value'] ?? 'Other';
                if (!in_array($type_name, $all_types)) {
                    if (count($all_types) < 4) {
                        $all_types[] = $type_name;
                    }
                }

                

                $bookmakers = isset($type['bookmaker'][0]) ? $type['bookmaker'] : [$type['bookmaker']];

               
                foreach ($bookmakers as $bm) {
                    $bm_name = $bm['name'] ?? 'Unknown';
                    if (!in_array($bm_name, $all_bookmakers)) {
                        $all_bookmakers[] = $bm_name;
                    }

                    $odds_list = isset($bm['odd'][0]) ? $bm['odd'] : [$bm['odd']];
                    // echo "<pre>";
                    // print_r($odds_list);
                    // echo "</pre>";
                    $match_odds[$type_name][$bm_name] = array_map(function($odd) {
                        // Extract numeric values only
                        $val = $odd['us'] ?? 'N/A';
                        $name = $odd['name'] ?? '';
                        return [
                            'name' => '', 
                            'value' => $val 
                        ];
                    }, $odds_list);
                }
            }

            $home_team = $match['home'] ?? [];
            $away_team = $match['away'] ?? [];          
            $home_score = (int)($match['home']['totalscore'] ?? 0);
            $away_score = (int)($match['away']['totalscore'] ?? 0);
            $total_score = $home_score + $away_score;

            // Time / Date parsing
                    $raw_date = $match['date'] ?? '';
                    $raw_time = $match['time'] ?? '';
                    
                    // Format Date
                    $fmt_date = $raw_date;
                    if (strtotime($raw_date)) {
                        $fmt_date = date('M jS', strtotime($raw_date));
                    } elseif(empty($fmt_date) && strtotime($raw_time)) {
                        $fmt_date = date('M jS', strtotime($raw_time));
                    } else {
                        $fmt_date = 'Today';
                    }

                    // Format Time
                    $fmt_time = $raw_time;
                    if (strtotime($raw_time)) {
                        $fmt_time = date('g:i A', strtotime($raw_time));
                    } else {
                        $fmt_time = $match['status'] ?? 'Scheduled';
                    }
          

            $structured_matches[] = [
                'id' => $match['id'] ?? uniqid(),
                'time' => $fmt_time,
                'date' => $fmt_date,
                'status' => $match['status'] ?? '',
                'home_name' => $home_team['name'] ?? $match['home_team'] ?? 'Home',
                'away_name' => $away_team['name'] ?? $match['away_team'] ?? 'Away',
                'home_logo' => $this->get_team_logo($home_team),
                'away_logo' => $this->get_team_logo($away_team),
                'home_score' => $home_score,
                'away_score' => $away_score,
                'total_score' => ($home_score || $away_score) ? $total_score : '0',
                'odds' => $match_odds
            ];


            
        }

        return [
            'types' => $all_types,
            'bookmakers' => $all_bookmakers,
            'matches' => $structured_matches
        ];
    }

    public function get_team_logo($team) {
        if (empty($team)) return '';

        // Case 1: Already a URL or Base64 string
        if (is_string($team)) {
            if (filter_var($team, FILTER_VALIDATE_URL) || strpos($team, 'data:image') === 0) return $team;
            if (strlen($team) > 100 && preg_match('/^[a-zA-Z0-9\/+]*={0,2}$/', $team)) return 'data:image/png;base64,' . $team;
            return '';
        }

        if (!is_array($team)) return '';

        // Case 2: Scan for direct keys in this array
        $keys = ['logo', 'image', 'team_logo', 'team_image', 'logo_url', 'home_logo', 'away_logo', 'url'];
        foreach ($keys as $key) {
            if (!empty($team[$key]) && is_string($team[$key])) {
                $val = $team[$key];
                if (filter_var($val, FILTER_VALIDATE_URL) || strpos($val, 'data:image') === 0) return $val;
                if (strlen($val) > 100) return 'data:image/png;base64,' . $val;
            }
        }

        // Case 3: Recursive shallow scan for nested objects
        foreach ($team as $child) {
            if (is_array($child)) {
                $found = $this->get_team_logo($child);
                if ($found) return $found;
            }
        }
    
        return '';
    }
}