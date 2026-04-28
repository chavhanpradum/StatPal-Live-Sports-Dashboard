<?php
if (!defined('ABSPATH')) exit;

/**
 * Template for rendering the Standings UI.
 * 
 * Variables available:
 * @var array $items Extracted team list.
 */
?>
<div class="statpal-livescores-container" data-paginated="true">
    
    <?php if (!empty($team_logo)) : ?>
        <div class="statpal-team-branding">
            <div class="statpal-team-branding-logo"><img src="<?php echo esc_attr($team_logo); ?>" alt="Team Logo"></div>
            <h3><?php echo esc_html($items[0]['_group'] ?? 'Standings'); ?></h3>
        </div>
    <?php endif; ?>

    <div class="statpal-ls-header">
        <div class="statpal-ls-header-col first">Rank</div>
        <div class="statpal-ls-header-col four">Team</div>
    </div>
    
    <?php 
    $rendered_count = 0;
   
    foreach ($items as $team) : 
        $rendered_count++;

        if (!is_array($team)) continue;

        $name = is_array($team) ? ($team['name'] ?? 'Unknown Row') : 'Unknown Row';
        $logo = $this->get_team_logo($team);
        
        $rank = $team['rank'] ?? $team['position'] ?? $rendered_count;
        $points = $team['points'] ?? $team['pts'] ?? '-';
        $wins = $team['won'] ?? $team['w'] ?? '-';
        $losses = $team['lost'] ?? $team['l'] ?? '-';
        $home_record = $team['home_record'] ?? $team['l'] ?? '-';
        $away_record = $team['away_record'] ?? $team['l'] ?? '-';
    ?>
        <div class="statpal-card statpal-page-item">
            <div class="statpal-ls-row">
                
                
                <div class="statpal-ls-row-col first">
                    <span class="day">Rank <?php echo esc_html($rank); ?></span></div>

                <div class="statpal-ls-row-col four">
                <div class="team-row-wrap">
                    
                        <div class="team-row">
                            <?php if($logo) : ?>
                                <div class="team-logo"><img src="<?php echo esc_attr($logo); ?>" alt="Logo"></div>
                            <?php else : ?>
                                <div class="team-circle"></div>
                            <?php endif; ?>
                            <div class="team-name"><?php echo esc_html($name); ?></div>
                        </div>
                    
                    </div>

                    <button type="button" class="statpal-btn-details statpal-info-btn for-desktop">Team Stats</button>
                    
                </div>
                <button type="button" class="statpal-btn-details statpal-info-btn  for-mobile">Team Stats</button>
            </div>
            
            <div class="statpal-card-details" style="display:none;">
                    <p><strong>Wins:</strong> <?php echo esc_html($wins); ?></p>
                    <p><strong>Losses:</strong> <?php echo esc_html($losses); ?></p>
                    <p><strong>Home Record :</strong> <?php echo esc_html($home_record); ?></p>
                    <p><strong>Away Record :</strong> <?php echo esc_html($away_record); ?></p>
            </div>
        </div>
    <?php endforeach; ?>
    
    <?php if($rendered_count === 0) : ?>
        <div class="statpal-empty">No Data Available</div>
    <?php endif; ?>
</div>
