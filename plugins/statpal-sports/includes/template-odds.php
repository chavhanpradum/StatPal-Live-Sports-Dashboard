<?php
if (!defined('ABSPATH')) exit;

/**
 * Redesigned Template for rendering the Odds UI.
 * 
 * Variables available:
 * @var array $matches Extracted match list (original).
 * @var array $structured_odds Structured odds data from prepare_structured_odds().
 * @var StatPal_Render $this The render class instance
 */

$types = $structured_odds['types'] ?? [];
if (empty($types)) {
    echo '<div class="statpal-empty">No Odds Data Available</div>';
    return;
}

$bookmakers = $structured_odds['bookmakers'] ?? [];
$structured_matches = $structured_odds['matches'] ?? [];
?>

<div class="statpal-odds-container statpal-odds-premium-container">
    
    <!-- Top Tabs for Odds Types -->
    <div class="statpal-odds-tabs">
        <div class="statpal-odds-tabs-list">
            <?php foreach ($types as $index => $type_name) : ?>
                <button class="statpal-odds-type-tab <?php echo $index === 0 ? 'is-active' : ''; ?>" 
                        data-type="<?php echo esc_attr($type_name); ?>">
                    <?php echo esc_html($type_name); ?>
                </button>
            <?php endforeach; ?>
        </div>
        
        <!-- <div class="statpal-odds-legend">
            <span class="legend-item"><span class="dot best"></span> Best Odds</span>
            <span class="legend-item"><span class="dot shortening"></span> Odds Shortening</span>
            <span class="legend-item"><span class="dot static"></span> Static Odds</span>
            <span class="legend-item"><span class="dot drifting"></span> Odds Drifting</span>
        </div> -->
    </div>

    <!-- Main Odds Table Wrapper -->
    <div class="statpal-odds-table-wrapper">
        <div class="statpal-odds-table">
            
            <!-- Table Header -->
            <div class="statpal-odds-thead">
                <div class="statpal-odds-info-header">
                    <div class="col-time">TIME</div>
                    <div class="col-games">GAMES</div>
                    
                </div>
                
                <div class="statpal-bookmakers-slider-container">
                    <div class="statpal-bookmakers-slider">
                        <?php foreach ($bookmakers as $bm_name) : ?>
                            <div class="statpal-bm-header" data-bm="<?php echo esc_attr($bm_name); ?>">
                                <?php 
                                    // Try to determine a background color or just use a generic dark one
                                    $bg_color = '#d0a43e'; // Default
                                    if(strpos(strtolower($bm_name), 'draftkings') !== false) $bg_color = '#044c2c';
                                    if(strpos(strtolower($bm_name), 'betmgm') !== false) $bg_color = '#000000';
                                    if(strpos(strtolower($bm_name), 'circa') !== false) $bg_color = '#003366';
                                ?>
                                <div class="bm-logo-wrap" style="background-color: <?php echo esc_attr($bg_color); ?>;">
                                    <span class="bm-name"><?php echo esc_html($bm_name); ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <?php if (count($bookmakers) > 4) : ?>
                        <button class="statpal-bm-nav next"><span class="dashicons dashicons-arrow-right-alt2"></span></button>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Table Body -->
            <div class="statpal-odds-tbody">
                <?php foreach ($structured_matches as $match) : ?>
                    <div class="statpal-match-row statpal-page-item" data-match-id="<?php echo esc_attr($match['id']); ?>">
                        
                        <!-- Game Info Column -->
                        <div class="statpal-match-info">
                            <div class="col-time">
                                <span class="day"><?php echo esc_html($match['date']); ?></span>
                                <strong class="time"><?php echo esc_html($match['time']); ?></strong>
                                <span class="status-str"><?php echo esc_html($match['status']); ?></span>
                            </div>
                            
                            <div class="col-games">
                                <div class="team-row-wrap">
                                    <div class="team home">
                                        <div class="team-meta">
                                            <?php if($match['away_logo']) : ?>
                                                <img src="<?php echo esc_attr($match['away_logo']); ?>" class="team-logo">
                                            <?php else : ?>
                                                <div class="team-logo-placeholder"></div>
                                            <?php endif; ?>
                                            <span class="team-name"><?php echo esc_html($match['away_name']); ?></span>
                                        </div>
                                        <span class="score"><?php echo esc_html($match['away_score']); ?></span>
                                    </div>
                                    <div class="team home">
                                        <div class="team-meta">
                                            <?php if($match['home_logo']) : ?>
                                                <img src="<?php echo esc_attr($match['home_logo']); ?>" class="team-logo">
                                            <?php else : ?>
                                                <div class="team-logo-placeholder"></div>
                                            <?php endif; ?>
                                            <span class="team-name disable"><?php echo esc_html($match['home_name']); ?></span>
                                        </div>
                                        <span class="score"><?php echo esc_html($match['home_score']); ?></span>
                                    </div>
                                </div>
                            </div>
 
                        </div>

                        <!-- Odds Data Column (Slidable) -->
                        <div class="statpal-match-odds-container">
                            <div class="statpal-match-odds-slider">
                                <?php foreach ($bookmakers as $bm_name) : ?>
                                    <div class="statpal-odds-box-wrap" data-bm="<?php echo esc_attr($bm_name); ?>">
                                        <?php foreach ($types as $type_name) : 
                                            $odds_items = $match['odds'][$type_name][$bm_name] ?? null;
                                            $is_active_type = ($type_name === $types[0]);
                                        ?>
                                            <div class="statpal-odds-type-content" data-type-ref="<?php echo esc_attr($type_name); ?>" 
                                                 style="display: <?php echo $is_active_type ? 'flex' : 'none'; ?>;">
                                                <?php if ($odds_items) : ?>
                                                    <div class="odds-display-box">
                                                        <?php foreach ($odds_items as $odd) : ?>
                                                            <div class="odd-val"><?php echo esc_html($odd['value']); ?></div>
                                                        <?php endforeach; ?>
                                                    </div>
                                                <?php else : ?>
                                                    <div class="odds-na">N/A</div>
                                                <?php endif; ?>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                    </div>
                <?php endforeach; ?>
            </div>

        </div>
    </div>
</div>

