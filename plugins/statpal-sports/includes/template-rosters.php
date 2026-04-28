<?php
if (!defined('ABSPATH')) exit;

/**
 * Template for rendering the Rosters UI.
 * 
 * Variables available:
 * @var array $items Extracted player list.
 */
?>
<div class="statpal-livescores-container" data-paginated="true">
    
    <?php if (!empty($team_logo)) : ?>
        <div class="statpal-team-branding">
            <img src="<?php echo esc_attr($team_logo); ?>" alt="Team Logo">
            <h3><?php echo esc_html($items[0]['_group'] ?? 'Team Roster'); ?></h3>
        </div>
    <?php endif; ?>
	
    <div class="statpal-ls-header">
        <div class="statpal-ls-header-col first">Pos</div>
        <div class="statpal-ls-header-col four">Player</div>
    </div>
    
    <?php 
    $rendered_count = 0;
    
    foreach ($items as $player) : 
        $rendered_count++;

        if (!is_array($player)) continue;

        $name = $player['name'] ?? $player['full_name'] ?? 'Unknown Player';
        $pos = $player['position'] ?? $player['pos'] ?? 'POS';
        $num = $player['number'] ?? $player['jersey'] ?? '-';
        $status = $player['status'] ?? 'Active';
        
    ?>
        <div class="statpal-card statpal-page-item">
            <div class="statpal-ls-row">
            <div class="statpal-ls-row-col first">
                    <span class="day"><?php echo esc_html($pos); ?></span>
                    <span class="time">#<?php echo esc_html($num); ?></span>
                </div>

               <div class="statpal-ls-row-col four">
                    <div class="team-row-wrap">
                        <div class="team-row">
                            <div class="team-name"><?php echo esc_html($name); ?></div>
                        </div>
                    </div>

                    <button type="button" class="statpal-btn-details statpal-info-btn for-desktop">Player Info</button>
                    
                </div>
                <button type="button" class="statpal-btn-details statpal-info-btn for-mobile">Player Info</button>
            </div>
            
            <div class="statpal-card-details" style="display:none;">
               
                    <p><strong>Status:</strong> <?php echo esc_html($status); ?></p>
                    <p><?php if (isset($player['height'])) : ?><strong>Height:</strong> <?php echo esc_html($player['height']); ?></p>
					<p><?php endif; ?>
                    <?php if (isset($player['weight'])) : ?><strong>Weight:</strong> <?php echo esc_html($player['weight']); ?></p>
					<p><?php endif; ?>
                    <?php if (isset($player['college'])) : ?><strong>College:</strong> <?php echo esc_html($player['college']); ?></p><?php endif; ?>
               
            </div>
        </div>
    <?php endforeach; ?>
    
    <?php if($rendered_count === 0) : ?>
        <div class="statpal-empty">No Data Available</div>
    <?php endif; ?>
</div>
