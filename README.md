# StatPal Live Sports Dashboard

A dynamic, API-driven WordPress plugin that provides real-time sports data including live scores, odds, schedules, standings, and team details across multiple sports.

---

## 🚀 Features

* Real-time sports data integration via StatPal API
* Multi-sport support (NFL, NBA, MLB, Soccer, etc.)
* Dynamic sport management from admin panel
* Live Odds with bookmaker data
* Live Scores, Schedules, Standings, Team Rosters & Injuries
* AJAX-based UI (no page reload)
* Pagination for large datasets
* Custom sport naming support
* Default sport selection option
* Easy integration using shortcode

---

## ⚙️ Installation

1. Download or clone this repository:

   ```
   git clone https://github.com/chavhanpradum/StatPal-Live-Sports-Dashboard.git
   ```
2. Upload the plugin folder to:

   ```
   wp-content/plugins/
   ```
3. Go to WordPress Admin → Plugins
4. Activate **StatPal Live Sports Dashboard**

---

## 🔑 Configuration

1. Navigate to plugin settings in admin panel
2. Add your **StatPal API Key**
3. Set global game limits for pagination
4. Enable/Disable sports as needed
5. Select a default sport (optional)

---

## 🖥️ Usage

Use the shortcode below to display the dashboard on any page:

```
[statpal_dashboard]
```

---

## 🔄 How It Works

* User selects a sport or tab (Odds, Live Scores, etc.)
* AJAX request is triggered
* Data is fetched from StatPal API
* UI updates dynamically without page reload

---

## ⚠️ Notes

* All data is fetched dynamically from StatPal API
* No hardcoded data is used
* Some APIs may not provide team/player images

---

## 🛠️ Tech Stack

* WordPress Plugin Development
* PHP (Backend)
* AJAX (jQuery)
* HTML, CSS, JavaScript

---

## 📌 Author

Developed by Pradum

---

## 📄 License

This project is licensed under the MIT License.
