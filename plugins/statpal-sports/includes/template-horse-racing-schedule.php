<?php
if (!defined('ABSPATH')) exit;

$tournaments = $data['scores']['tournament'] ?? [];
if (is_array($tournaments) && !isset($tournaments[0]) && !empty($tournaments)) {
    $tournaments = [$tournaments];
}
?>

<div class="statpal-odds-container statpal-odds-premium-container" data-paginated="true">
    <?php if (empty($tournaments)): ?>
        <div class="statpal-empty">Data Not Available</div>
    <?php else: ?>
        
        <div class="statpal-odds-tabs">
        <div class="statpal-odds-tabs-list">
            <?php foreach ($tournaments as $idx => $tournament) : 
                $t_name = $tournament['name'] ?? 'Tournament ' . ($idx + 1);
            ?>
                <button class="statpal-odds-type-tab <?php echo $idx === 0 ? 'is-active' : ''; ?>" data-target="tourn-<?php echo esc_attr($idx); ?>">
                    <?php echo esc_html($t_name); ?>
                </button>
            <?php endforeach; ?>
        </div>
        </div>
        
        <div class="statpal-odds-table-wrapper">
        <?php foreach ($tournaments as $idx => $tournament) : 
            $t_name = $tournament['name'] ?? 'Tournament ' . ($idx + 1);
            $going = $tournament['going'] ?? '';
            $raw_date = $tournament['date'] ?? '';
            
            $races = $tournament['race'] ?? [];
            if (is_array($races) && !isset($races[0]) && !empty($races)) {
                $races = [$races];
            }

            $fmt_date = $raw_date;
            if (strtotime($raw_date)) {
                $fmt_date = date('M jS', strtotime($raw_date));
            } elseif(empty($fmt_date)) {
                 $fmt_date = date('M jS');
            } else {
                 $fmt_date = 'Today';
            }
        ?>
            <div id="tourn-<?php echo esc_attr($idx); ?>" class="statpal-hr-tournament" style="display: <?php echo $idx === 0 ? 'block' : 'none'; ?>;">
                <div class="statpal-hr-t-header" style="background: #b59243; color: #fff; padding: 12px 15px; display: flex; justify-content: space-between; align-items: center;">
                    <div class="statpal-hr-t-header-left">
                        <h3 style="margin: 0; font-size: 12px; font-weight: 700; color: #fff;"><?php echo esc_html($t_name); ?></h3>
                    </div>
                    <div class="statpal-hr-t-header-right" style="font-size: 11px; font-weight: 600; background: rgba(255,255,255,0.2); padding: 4px 10px; border-radius: 20px;">
                        <?php echo esc_html($fmt_date); ?><?php echo $going ? ' &bull; ' . esc_html($going) : ''; ?>
                    </div>
                </div>

                <?php 
                if (empty($races)) {
                    echo '<div class="statpal-empty">Data Not Available</div>';
                    continue;
                }

                foreach ($races as $race) :
                    $race_name = $race['name'] ?? 'Race';
                    $raw_time = $race['time'] ?? 'TBD';
                    $distance = $race['distance'] ?? '-';
                    $race_class = $race['class'] ?? '';
                    
                    $fmt_time = $raw_time;
                    if (strtotime($raw_time)) $fmt_time = date('g:i A', strtotime($raw_time));
                    
                    $results = $race['results']['horse'] ?? [];
                    if (is_array($results) && !isset($results[0]) && !empty($results)) $results = [$results];
                    
                    $runners = $race['runners']['horse'] ?? [];
                    if (is_array($runners) && !isset($runners[0]) && !empty($runners)) $runners = [$runners];

                    $odds_data = $race['odds']['horse'] ?? [];
                    if (is_array($odds_data) && !isset($odds_data[0]) && !empty($odds_data)) $odds_data = [$odds_data];
                    
                    $odds_map = [];
                    $all_bookmakers = [];
                    foreach ($odds_data as $oh) {
                        // Match primarily by ID
                        $hid = $oh['id'] ?? '';
                        if ($hid) {
                            $bks = $oh['bookmakers']['bookmaker'] ?? [];
                            if (is_array($bks) && !isset($bks[0]) && !empty($bks)) $bks = [$bks];
                            
                            $odds_map[$hid] = [];
                            foreach($bks as $b) {
                                $bname = $b['name'] ?? 'Unknown';
                                $odds_map[$hid][$bname] = $b['odd'] ?? '-';
                                if(!in_array($bname, $all_bookmakers)) $all_bookmakers[] = $bname;
                            }
                        }
                    }
                    
                    $display_horses = !empty($results) ? $results : $runners;
                ?>
                <div class="statpal-card statpal-page-item" >
                    <div class="statpal-ls-row">
                        <div class="statpal-ls-row-col first">
                            <span class="time"><?php echo esc_html($fmt_time); ?></span>
                            <span class="status-str"><?php echo esc_html($distance); ?></span>
                        </div>
                        <div class="statpal-ls-row-col four">
                            <div class="team-row-wrap">
                            <div class="team-row" style="display:block">
                                <div class="team-name"><?php echo esc_html($race_name); ?></div>
                                <div class="statpal-hr-race-meta">
                                    <?php if($race_class) echo 'Class ' . esc_html($race_class) . ' &bull; '; ?>
                                    <?php echo count($display_horses); ?> Runners
                                </div>
                                </div>
                            </div>
                            <button type="button" class="statpal-btn-details statpal-info-btn for-desktop">Race Info</button>
                        </div>
                        <button type="button" class="statpal-btn-details statpal-info-btn for-mobile">Race Info</button>
                    </div>
                    
                    <div class="statpal-card-details dark-arrow" style="display:none;padding: 0;background: transparent;">
                    <div class="statpal-odds-table-wrapper" style="padding:0;">
                        <?php if (empty($display_horses)): ?>
                            <div class="statpal-empty">Data Not Available</div>
                        <?php else: ?>
                                <div class="statpal-odds-table large-table">
                                    <div class="statpal-odds-thead">
                                        <div class="statpal-odds-info-header">
                                           
                                                <div class="col-number" style="text-align:left;">NO.</div>
                                                <div class="col-horse">HORSE</div>
                                                <div class="col-team">TEAM</div>
                                                <div class="col-form"><?php echo !empty($results) ? 'SP / TIME' : 'FORM'; ?></div>
                                            
                                        </div>
                                        
                                        <div class="statpal-bookmakers-slider-container">
                                            <div class="statpal-bookmakers-slider">
                                                <?php if(empty($all_bookmakers)): ?>
                                                    <div class="statpal-bm-header">
                                                    <div class="bm-logo-wrap"><span>Odds</span></div></div>
                                                <?php else: ?>
                                                    <?php foreach($all_bookmakers as $bm_name): ?>
                                                        <div class="statpal-bm-header" data-bm="<?php echo esc_attr($bm_name); ?>">
                                                            <div class="bm-logo-wrap" style="background-color: #d0a43e;">
                                                                <span class="bm-name"><?php echo esc_html($bm_name); ?></span>
                                                            </div>
                                                        </div>
                                                    <?php endforeach; ?>
                                                <?php endif; ?>
                                            </div>
                                            <?php if (count($all_bookmakers) > 1) : ?>
                                                <button type="button" class="statpal-bm-nav next"><span class="dashicons dashicons-arrow-right-alt2"></span></button>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <div class="statpal-odds-tbody">
                                        <?php foreach($display_horses as $horse): 
                                            $h_id = $horse['id'] ?? '';
                                            $h_name = $horse['name'] ?? 'Unknown';
                                            $pos = $horse['pos'] ?? $horse['number'] ?? '-';
                                            $jockey = $horse['jockey'] ?? '-';
                                            $trainer = $horse['trainer'] ?? '-';
                                            $age = $horse['age'] ?? '';
                                            $weight = $horse['wgt_lbs'] ?? $horse['wgt'] ?? '';
                                            
                                            // Mapping from runners if results
                                            if (!empty($results) && !empty($runners)) {
                                                foreach($runners as $r) {
                                                    if(isset($r['id']) && $h_id === $r['id']) {
                                                        if($jockey === '-') $jockey = $r['jockey'] ?? '-';
                                                        if($trainer === '-') $trainer = $r['trainer'] ?? '-';
                                                        if(!$age) $age = $r['age'] ?? '';
                                                        if(!$weight) $weight = $r['wgt_lbs'] ?? $r['wgt'] ?? '';
                                                    }
                                                }
                                            }

                                            $meta_parts = [];
                                            if ($age) $meta_parts[] = $age . 'yo';
                                            if ($weight) $meta_parts[] = $weight . 'lbs';
                                            $meta_str = implode(' &bull; ', $meta_parts);

                                            $form_str = '-';
                                            if (isset($horse['recent_form']['section'])) {
                                                $sections = $horse['recent_form']['section'];
                                                if (is_array($sections) && !isset($sections[0]) && !empty($sections)) $sections = [$sections];
                                                foreach ($sections as $s) {
                                                    if (($s['name'] ?? '') === 'race record') {
                                                        $stats = $s['stat'] ?? [];
                                                        if (is_array($stats) && !isset($stats[0]) && !empty($stats)) $stats = [$stats];
                                                        foreach($stats as $st) {
                                                            if(($st['name'] ?? '') === 'All Flat Races' || ($st['name'] ?? '') === 'Flat') {
                                                                $pct = $st['win_pct'] ?? '-';
                                                                if ($pct !== '-' && $pct !== '0%') {
                                                                    $form_str = 'Win: ' . $pct;
                                                                    break 2;
                                                                }
                                                            }
                                                        }
                                                    }
                                                }
                                            }
                                        ?>
                                        <div class="statpal-match-row statpal-page-item">
                                            <div class="statpal-match-info">
                                                
                                                    <div class="col-number"><span class="statpal-hr-runner-no"><?php echo esc_html($pos); ?></span></div>
                                                    <div class="col-horse">
                                                        <div class="statpal-hr-runner-name"><?php echo esc_html($h_name); ?></div>
                                                        <?php if($meta_str): ?>
                                                            <div class="statpal-hr-runner-meta"><?php echo esc_html($meta_str); ?></div>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="col-team">
                                                        <div class="statpal-hr-jt-item"><span class="statpal-hr-jt-label">J:</span><?php echo esc_html($jockey); ?></div>
                                                        <div class="statpal-hr-jt-item"><span class="statpal-hr-jt-label">T:</span><?php echo esc_html($trainer); ?></div>
                                                    </div>
                                                    <div class="col-form">
                                                        <?php if (!empty($results)): ?>
                                                            <div><?php echo esc_html($horse['sp'] ?? '-'); ?></div>
                                                            <div style="margin-top:5px;"><?php echo esc_html($horse['time'] ?? ''); ?></div>
                                                        <?php else: ?>
                                                            <div><?php echo esc_html($form_str); ?></div>
                                                        <?php endif; ?>
                                                    </div>
                                               
                                            </div>
                                            
                                            <div class="statpal-match-odds-container">
                                                <div class="statpal-match-odds-slider">
                                                    <?php if(empty($all_bookmakers)): ?>
                                                        <div class="statpal-odds-box-wrap" style="flex:1;">
                                                            <div class="statpal-odds-type-content">
                                                                <div class="odds-na">N/A</div>
                                                            </div>
                                                        </div>
                                                    <?php else: ?>
                                                        <?php foreach($all_bookmakers as $bm_name): 
                                                            $bm_odd = $odds_map[$h_id][$bm_name] ?? null;
                                                        ?>
                                                            <div class="statpal-odds-box-wrap" data-bm="<?php echo esc_attr($bm_name); ?>">
                                                                <div class="statpal-odds-type-content" data-type-ref="RaceWinner">
                                                                    <?php if ($bm_odd) : ?>
                                                                        <div class="odds-display-box">
                                                                            <div class="odd-val"><?php echo esc_html($bm_odd); ?></div>
                                                                        </div>
                                                                    <?php else : ?>
                                                                        <div class="odds-na">N/A</div>
                                                                    <?php endif; ?>
                                                                </div>
                                                            </div>
                                                        <?php endforeach; ?>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                        <?php endif; ?>
                    </div>
                </div>
                </div>
                <?php endforeach; // End Races Loop ?>
            </div>
        <?php endforeach; // End Tournaments Loop ?>
        </div>
        
    <?php endif; ?>
</div>
