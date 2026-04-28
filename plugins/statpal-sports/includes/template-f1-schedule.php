<?php
if (!defined('ABSPATH')) exit;

/**
 * Formula 1 Schedule Template
 * 
 * Variables available:
 * @var array $data Full API response payload.
 */

$tournaments = $data['fixtures']['tournament'] ?? [];

// Normalize
if (is_array($tournaments) && !isset($tournaments[0]) && !empty($tournaments)) {
    $tournaments = [$tournaments];
}
?>
<div class="statpal-livescores-container" data-paginated="true">

    <div class="statpal-ls-header">
        <div class="statpal-ls-header-col first">Time</div>
        <div class="statpal-ls-header-col four">RACES</div>
    </div>
    
    <?php 
    $rendered_count = 0;
    foreach ($tournaments as $gp) : 
        $rendered_count++;
        $name = $gp['name'] ?? 'Race';
        $race_info = $gp['race'] ?? [];
        
        $date = $race_info['date'] ?? 'TBD';
        $time = $race_info['time'] ?? 'TBD';
        $track = $race_info['track'] ?? '-';
        $status = $race_info['status'] ?? '-';
        $distance = $race_info['distance'] ?? '-';
        $laps = $race_info['total_laps'] ?? '-';
        $city = $race_info['city'] ?? '';
    ?>
        <div class="statpal-card statpal-page-item">
            <div class="statpal-ls-row">
                
                <div class="statpal-ls-row-col first">
                    <span class="day"><?php echo esc_html($date); ?></span>
                    <span class="time"><?php echo esc_html($time); ?></span>
                </div>
                

               <div class="statpal-ls-row-col four">
                    <div class="team-row-wrap">
                        <div class="team-name"><?php echo esc_html($name); ?> <?php if($city) : ?><span style="font-size:12px; color:#657786;">(<?php echo esc_html($city); ?>)</span><?php endif; ?></div>
                        <span style="font-size:13px; color:#657786;"><?php echo esc_html($track); ?></span>
                    </div>

                     <button type="button" class="statpal-btn-details statpal-info-btn for-desktop">Race Info</button>
                </div>
                <button type="button" class="statpal-btn-details statpal-info-btn for-mobile">Race Info</button>
            </div>
            
            <div class="statpal-card-details" style="display:none;">
                <div>
                    <p><strong>Status:</strong> <?php echo esc_html($status); ?></p>
                    <p><strong>Distance:</strong> <?php echo esc_html($distance); ?> km</p>
                    <p><strong>Laps:</strong> <?php echo esc_html($laps); ?></p>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
    
    <?php if($rendered_count === 0) : ?>
        <div class="statpal-empty">No Schedule Available</div>
    <?php endif; ?>
</div>
