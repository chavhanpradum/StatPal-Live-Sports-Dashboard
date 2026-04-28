<?php
if (!defined('ABSPATH')) exit;

/**
 * Template for rendering the Injuries UI.
 * 
 * Variables available:
 * @var array $items Extracted player list.
 */
?>
<div class="statpal-livescores-container" data-paginated="true">
    
    <?php if (!empty($team_logo)) : ?>
        <div class="statpal-team-branding">
            <img src="<?php echo esc_attr($team_logo); ?>" alt="Team Logo">
            <h3><?php echo esc_html($items[0]['_group'] ?? 'Team Injuries'); ?></h3>
        </div>
    <?php endif; ?>

    <div class="statpal-ls-header">
        <div class="statpal-ls-header-col first">Status</div>
        <div class="statpal-ls-header-col four">Teams</div>
    </div>
    
    <?php 
    $rendered_count = 0;
    
    foreach ($items as $player) : 
        $rendered_count++;

        if (!is_array($player)) continue;

        $name = $player['name'] ?? $player['full_name'] ?? 'Unknown Player';
        $status = $player['status'] ?? $player['desc'] ?? 'Injured';
        $pos = $player['pos'] ?? $player['position'] ?? '-';
        $return = $player['return_date'] ?? $player['expected_return'] ?? 'TBD';
        
    ?>
        <div class="statpal-card statpal-page-item">
            <div class="statpal-ls-row">
                
                <div class="statpal-ls-row-col first">
                    <span class="day danger"><?php echo esc_html(strtoupper(substr($status, 0, 8))); ?></span>
                    
                </div>

               <div class="statpal-ls-row-col four">
                    <div class="team-row-wrap">
                        <div class="team-row">
                            <div class="team-name"><?php echo esc_html($name); ?></div>
                        </div>
                    </div>

                    <button type="button" class="statpal-btn-details statpal-info-btn for-desktop">Injury Details</button>
                    
                </div>
                <button type="button" class="statpal-btn-details statpal-info-btn for-mobile">Injury Details</button>
            </div>
            
            <div class="statpal-card-details" style="display:none;">
            
                <div class="injury-table-wrap">  
                    <table class="injury-table" width="100%" border="1" style="border-collapse:collapse;">
                        <thead>
                            <tr style="text-align: left;">
                                <th>Player Name</th>
                                <th>Status</th>
                                <th>Description</th>
                            </tr>
                        </thead>
                        <?php foreach($player['report'] as $team) : ?>
                        <tr>
                            <td><?php echo esc_html($team['player_name']); ?></td>
                            <td><?php echo esc_html($team['status']); ?></td>
                            <td><?php echo esc_html($team['description']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </table>                    
                        
                    </div>
            
            </div>
        </div>
    <?php endforeach; ?>
    
    <?php if($rendered_count === 0) : ?>
        <div class="statpal-empty">No Data Available</div>
    <?php endif; ?>
</div>
