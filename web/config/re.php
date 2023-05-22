<?php

return [
    "game_demo" => env("RE_GAME_DEMO", false), // Use only for game demo @ rb.codetool.net

    // Maximal level (to seed content)
    "max_level" => env("RE_MAX_LEVEL", 10),
    "xp_start" => env("RE_XP_START", 50), // First level XP_MAX
    "xp_ratio" => env("RE_XP_RATIO", 1.2), // Increase ratio (each level) for XP_MAX
    "add_points_base" => env("RE_ADD_POINTS_BASE", 15), // How many (add_points) given for new level
    "add_points_ratio" => env("RE_ADD_POINTS_RATIO", 1), // Increase ratio (each level) for add_points
    
    // Renew rate
    "hp_rate" => env("RE_HP_RATE", 2), // How long it takes to renew 1HP (sec)
    "ap_rate" => env("RE_AP_RATE", 5), // How long it takes to renew 1AP (sec)

    // Renew COST - How much it cost to renew HP/AP
    "renew_hp_gems" => env("RE_RENEW_HP_GEMS", 2), // Renew HP (gems)
    "renew_ap_gems" => env("RE_RENEW_AP_GEMS", 2), // Renew AP (gems)

    // Default Avatar IMG url
    "user_img_url" => env("RE_USER_IMG_URL", "/themes/x/assets/img/avatar_default.png"), // Default avatar url

    // XP Ratio (Opponent HP points * RATIO = XP Points)
    "xp_win_ratio" => env("RE_XP_WIN_RATIO", 0.2), // 20%
    "xp_standoff_ratio" => env("RE_XP_STANDOFF_RATIO", 0.1), // 10%

    // Shop
    "stripe_private_key" => env("RE_STRIPE_PRIVATE_KEY", ""), // You stripe private key
    "stripe_webhook_secret" => env("RE_STRIPE_WEBHOOK_SECRET", ""), // You stripe webhook secret (key)
    // COINS
    "coins_price_id" => env("RE_COINS_PRICE_ID", ""), // Stripe Price ID for Coins (empty = disabled)
    "coins_bundle_price" => env("RE_COINS_BUNDLE_PRICE", 1.00), // i.e. 1USD for coin package (must be same as in stripe)
    "coins_bundle_size" => env("RE_COINS_BUNDLE_SIZE", 1000), // How much coins in one bundle
    // GEMS
    "gems_price_id" => env("RE_GEMS_PRICE_ID", ""), // Stripe Price ID for Coins (empty = disabled)
    "gems_bundle_price" => env("RE_GEMS_BUNDLE_PRICE", 1.00), // i.e. 1USD for coin package (must be same as in stripe)
    "gems_bundle_size" => env("RE_GEMS_BUNDLE_SIZE", 50), // How much Gems in one bundle

    // Market
    // How many items can be put on market (sell), must devide by 8 (row size)
    'sell_slots' => env("RE_SELL_SLOT", 40),
    
    // Bank
    // How many items can be put in safe, must devide by 8 (row size)
    'safe_slots' => env("RE_SAFE_SLOT", 40),
    // How many items can be pledged, must devide by 8 (row size)
    'pledge_slots' => env("RE_PLEDGE_SLOT", 40),
    


    // Stats increase logic when 
    "stats" => [
        "hp_base" => env("RE_HP_BASE", 0), // User Base
        "hp_increase" => env("RE_HP_INCREASE", 20), // User Level incr.
        "ap_base" => env("RE_AP_BASE", 0), // User Base
        "ap_increase" => env("RE_AP_INCREASE", 5), // User Level incr.
        "power_base" => env("RE_POWER_BASE", 0), // User Base
        "power_increase" => env("RE_POWER_INCREASE", 3), // User Level incr.
        "weapon_base" => env("RE_WEAPON_BASE", 0),// Seed Item incr.
        "weapon_increase" => env("RE_WEAPON_INCREASE", 6),// Seed Item incr.
        "defense_base" => env("RE_DEFENSE_BASE", 0), // User Base
        "defense_increase" => env("RE_DEFENSE_INCREASE", 2), // User Level incr.
        "shield_base" => env("RE_SHIELD_BASE", 0), // Seed Item incr.
        "shield_increase" => env("RE_SHIELD_INCREASE", 2), // Seed Item incr.
        "defense_item_base" => env("RE_DEFENSE_ITEM_BASE", 0), // defense item (helmet, armor, pants, boots)
        "defense_item_increase" => env("RE_DEFENSE_ITEM_INCREASE", 1), // defense item (helmet, armor, pants, boots)
        "storage_base" => env("RE_STORAGE_BASE", 40), // Base storage
    ],

];
