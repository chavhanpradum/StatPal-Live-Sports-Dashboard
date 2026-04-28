<?php
if (!defined('ABSPATH')) exit;

/**
 * Formula 1 Standings Template
 * 
 * Variables available:
 * @var array $data Full API response payload.
 */

$teams = $data['standings']['teams']['team'] ?? [];

// Normalize
if (is_array($teams) && !isset($teams[0]) && !empty($teams)) {
    $teams = [$teams];
}

$team_logo = '';
if (isset($data['image'])) {
    $team_logo = $data['image'];
} elseif (isset($data['team']['logo'])) {
    $team_logo = $data['team']['logo'];
}
?>
<div class="statpal-livescores-container" data-paginated="true">
    
    <?php if (!empty($team_logo)) : ?>
        <div class="statpal-team-branding">
            <div class="statpal-team-branding-logo"><img src="<?php echo esc_attr($team_logo); ?>" alt="Team Logo"></div>
            <h3>F1 Constructor Standings</h3>
        </div>
    <?php endif; ?>

<div class="statpal-ls-header">
        <div class="statpal-ls-header-col first">Rank</div>
        <div class="statpal-ls-header-col four">Constructor (Team)</div>
    </div>
    
    <?php 
    $rendered_count = 0;
    foreach ($teams as $team) : 
        $rendered_count++;

        if (!is_array($team)) continue;

        $name = $team['name'] ?? 'Unknown Team';
        $logo = method_exists($this, 'get_team_logo') ? $this->get_team_logo($team) : '';
        
        $rank = $team['post'] ?? $team['pos'] ?? $team['rank'] ?? $rendered_count;
        $points = $team['points'] ?? $team['pts'] ?? '-';
    ?>
        <div class="statpal-card statpal-page-item">
            <div class="statpal-ls-row">
                
                <div class="statpal-ls-row-col first">
                    <span class="day">Rank <?php echo esc_html($rank); ?></span>
                    <span class="time">PTS: <?php echo esc_html($points); ?></span>
                </div>

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
                <div>
                    <p><strong>F1 Constructor</strong></p>
                    <p><strong>Total Points:</strong> <?php echo esc_html($points); ?></p>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
    
    <?php if($rendered_count === 0) : ?>
        <div class="statpal-empty">No Standings Available</div>
    <?php endif; ?>
</div>
