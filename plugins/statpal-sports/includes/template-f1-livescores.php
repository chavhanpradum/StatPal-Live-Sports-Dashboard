<?php
if (!defined('ABSPATH')) exit;

/**
 * Formula 1 Livescores Template
 * 
 * Variables available:
 * @var array $data Full API response payload.
 */

$tournament = isset($data['livescore']['tournament'][0]) ? $data['livescore']['tournament'][0] : ($data['livescore']['tournament'] ?? []);
$race = $tournament['race'] ?? [];
$drivers = $race['results']['driver'] ?? [];

// In case driver is a single object, normalize it to an array of objects
if (is_array($drivers) && !isset($drivers[0]) && !empty($drivers)) {
    $drivers = [$drivers];
}

$race_name = $tournament['name'] ?? 'Formula 1 Grand Prix';
$date = $race['date'] ?? '';
$time = $race['time'] ?? '';
$status = $race['status'] ?? 'Scheduled';
$track = $race['track'] ?? '';
$laps = $race['total_laps'] ?? '-';
?>
<div class="statpal-livescores-container" data-paginated="true">
    <div class="statpal-ls-header">
        <div class="statpal-ls-header-col first">Pos</div>
        <div class="statpal-ls-header-col four">Driver</div>
    </div>
    
    <?php if (empty($drivers)) : ?>
        <div class="statpal-empty">No Race Results Available</div>
    <?php else : ?>
        <div style="padding:15px; background:#f8f9fa; border-bottom:1px solid #e1e8ed; text-align:center;">
            <strong style="font-size:16px; color:#14171a;"><?php echo esc_html($race_name); ?></strong><br>
            <span style="font-size:12px; color:#657786;"><?php echo esc_html($track); ?> | Status: <?php echo esc_html($status); ?> | Laps: <?php echo esc_html($laps); ?></span>
        </div>

        <?php foreach ($drivers as $driver) : 
            $pos = $driver['pos'] ?? '-';
            $name = $driver['name'] ?? 'Unknown Driver';
            $team = $driver['team'] ?? '-';
            $driver_time = $driver['time'] ?? '-';
            $laps_done = $driver['laps'] ?? '-';
            
            // Format driver name slightly robustly
            $parts = explode('(', $name);
            $clean_name = trim($parts[0]);
            $country = isset($parts[1]) ? trim(str_replace(')', '', $parts[1])) : '';
        ?>
            <div class="statpal-card statpal-page-item">
                <div class="statpal-ls-row">
                    
                    <div class="statpal-ls-row-col first">
                        <span class="time">#<?php echo esc_html($pos); ?></span>
                    </div>

                    <div class="statpal-ls-row-col four">
                        <div class="team-row-wrap">
                        
                            <div class="team-name"><?php echo esc_html($clean_name); ?> <?php if($country): ?><span style="font-size:11px; color:#657786;">(<?php echo esc_html($country); ?>)</span><?php endif; ?></div>

                            <span style="font-size:13px; color:#657786;"><?php echo esc_html($team); ?></span>
                       
                        </div>
                        <button type="button" class="statpal-btn-details statpal-info-btn for-desktop">Race Info</button>
                    </div>
                    <button type="button" class="statpal-btn-details statpal-info-btn for-mobile">Race Info</button>
                </div>
                
                <div class="statpal-card-details" style="display:none;">
                    <div>
                        <p><strong>Laps Completed:</strong> <?php echo esc_html($laps_done); ?></p>
                        <p><strong>Time/Gap:</strong> <?php echo esc_html($driver_time); ?></p>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
