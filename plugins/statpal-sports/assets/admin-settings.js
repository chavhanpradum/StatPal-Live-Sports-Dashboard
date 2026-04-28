document.addEventListener('DOMContentLoaded', () => {
    const listContainer = document.getElementById('statpal-sports-list');
    const inputAdd = document.getElementById('statpal-new-sport');
    const btnAdd = document.getElementById('statpal-btn-add');
    const hiddenInput = document.getElementById('statpal_active_sports_data');
    
    // Parse Initial Data
    let sportsData = {};
    try {
        const raw = hiddenInput.value;
        sportsData = JSON.parse(raw);
    } catch(e) {
        sportsData = { sports: { nfl: { enabled: true }, mlb: { enabled: true }, nhl: { enabled: true } }, default_sport: 'nfl' };
    }

    if (!sportsData.sports) sportsData.sports = {};

    const saveState = () => {
        hiddenInput.value = JSON.stringify(sportsData);
    };

    const renderList = () => {
        listContainer.innerHTML = '';
        const keys = Object.keys(sportsData.sports).sort();

        keys.forEach(key => {
            const sport = sportsData.sports[key];
            const isDefault = sportsData.default_sport === key;
            const isEnabled = sport.enabled;

            const customName = sport.custom_name || '';
            const logoUrl = `/wp-content/plugins/statpal-sports/assets/images/${key}.png`;

            const div = document.createElement('div');
            div.className = `statpal-sport-item ${isDefault ? 'is-default' : ''}`;

            div.innerHTML = `
                <div class="statpal-sport-info" style="display: flex; align-items: center; gap: 10px;">
                    <img src="${logoUrl}" onerror="this.outerHTML='<span class=\\'dashicons dashicons-chart-bar\\' style=\\'color:#aab8c2;\\'></span>'" alt="logo" style="width:24px; height:24px; object-fit:contain;" />
                    <span class="statpal-sport-name">${key}</span>
                    <input type="text" class="statpal-custom-name-input" data-key="${key}" value="${customName}" placeholder="Display Name (e.g. Formula 1)" style="font-size:13px; padding:4px 8px; max-width:180px;">
                </div>
                <div class="statpal-controls">
                    <label class="statpal-check-lbl">
                        <input type="checkbox" class="cb-enable" data-key="${key}" ${isEnabled ? 'checked' : ''}>
                        Enabled
                    </label>
                    <label class="statpal-radio-lbl">
                        <input type="radio" name="statpal_default_sport" class="rb-default" data-key="${key}" ${isDefault ? 'checked' : ''}>
                        Default
                    </label>
                    <button type="button" class="statpal-btn-delete" data-key="${key}">Delete</button>
                </div>
            `;
            listContainer.appendChild(div);
        });
        saveState();
    };

    // Event Delegation
    listContainer.addEventListener('change', (e) => {
        if (e.target.classList.contains('cb-enable')) {
            const key = e.target.dataset.key;
            sportsData.sports[key].enabled = e.target.checked;
            saveState();
        }
        if (e.target.classList.contains('rb-default')) {
            sportsData.default_sport = e.target.dataset.key;
            renderList(); // Re-render to highlight correctly
        }
    });

    listContainer.addEventListener('input', (e) => {
        if (e.target.classList.contains('statpal-custom-name-input')) {
            const key = e.target.dataset.key;
            sportsData.sports[key].custom_name = e.target.value.trim();
            saveState();
        }
    });

    listContainer.addEventListener('click', (e) => {
        if (e.target.classList.contains('statpal-btn-delete')) {
            const key = e.target.dataset.key;
            if (confirm(`Are you sure you want to delete ${key.toUpperCase()}?`)) {
                delete sportsData.sports[key];
                if (sportsData.default_sport === key) {
                    const remaining = Object.keys(sportsData.sports);
                    sportsData.default_sport = remaining.length > 0 ? remaining[0] : '';
                }
                renderList();
            }
        }
    });

    btnAdd.addEventListener('click', () => {
        const val = inputAdd.value.trim().toLowerCase();
        if (!val) return;
        if (sportsData.sports[val]) {
            alert('Sport already exists!');
            return;
        }

        sportsData.sports[val] = { enabled: true };
        if (!sportsData.default_sport) sportsData.default_sport = val;
        
        inputAdd.value = '';
        renderList();
    });

    inputAdd.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') {
            e.preventDefault();
            btnAdd.click();
        }
    });

    // Initial render
    renderList();
});
