(() => {
    let currentPage = 1;
    let itemsPerPage = 10;
    let paginatedItems = [];

    const handlePagination = (contentArea) => {
        const nextBtn = contentArea.querySelector('.statpal-next-page');
        const prevBtn = contentArea.querySelector('.statpal-prev-page');
        const currPageLabel = contentArea.querySelector('.current-page');
        const paginationControls = contentArea.querySelector('.statpal-pagination-controls');

        const totalItems = paginatedItems.length;
        if (totalItems <= itemsPerPage) {
            paginationControls.style.display = 'none';
            paginatedItems.forEach(item => item.style.display = '');
            return;
        }

        paginationControls.style.display = 'flex';
        const totalPages = Math.ceil(totalItems / itemsPerPage);
        
        paginatedItems.forEach((item, index) => {
            if (index >= (currentPage - 1) * itemsPerPage && index < currentPage * itemsPerPage) {
                item.style.display = '';
            } else {
                item.style.display = 'none';
            }
        });

        currPageLabel.textContent = `${currentPage} / ${totalPages}`;
        prevBtn.disabled = currentPage === 1;
        nextBtn.disabled = currentPage === totalPages;
    };

    const buildParamsBar = (paramsString, dashboard, submitCallback) => {
        const paramsBar = dashboard.querySelector('#statpal-params-bar');
        paramsBar.innerHTML = '';
        let endpointParams = {};
        if (paramsString) {
            try { endpointParams = JSON.parse(paramsString) || {}; } catch(e) {}
        }
        
        if (Object.keys(endpointParams).length === 0) {
            paramsBar.style.display = 'none';
            return;
        }

        paramsBar.style.display = 'flex';
        
        for (const [pKey, pVal] of Object.entries(endpointParams)) {
            if(pVal.type === 'select') {
                const sel = document.createElement('select');
                sel.className = 'statpal-param-select statpal-param-input-val';
                sel.dataset.key = pKey;
                for (const [optVal, optLabel] of Object.entries(pVal.options)) {
                    const opt = document.createElement('option');
                    opt.value = optVal;
                    opt.textContent = optLabel;
                    if(pVal.default === optVal) opt.selected = true;
                    sel.appendChild(opt);
                }
                sel.addEventListener('change', () => submitCallback());
                paramsBar.appendChild(sel);
            } else if (pVal.type === 'text') {
                const input = document.createElement('input');
                input.className = 'statpal-param-text statpal-param-input-val';
                input.type = 'text';
                input.placeholder = pVal.label || '';
                input.dataset.key = pKey;
                input.value = pVal.default || 'ari'; // Default placeholder/team code
                
                input.addEventListener('keypress', (e) => {
                    if (e.key === 'Enter') submitCallback();
                });
                
                paramsBar.appendChild(input);
            }
        }
        
        const hasText = Object.values(endpointParams).some(p => p.type === 'text');
        if (hasText) {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'statpal-btn-page';
            btn.textContent = 'Fetch';
            btn.addEventListener('click', () => submitCallback());
            paramsBar.appendChild(btn);
        }
    };

    const fetchData = async (sportId, tabId, rawEndpoint, contentArea, tabElement) => {
        const resultsArea = contentArea.querySelector('.statpal-content-results');
        const overlay = contentArea.querySelector('.statpal-loading-overlay');
        const sportName = tabElement.closest('.statpal-sport-item').querySelector('.statpal-sport-name').textContent;
        const tabName = tabElement.textContent;

        const headerSport = document.getElementById('statpal-active-sport');
        const headerTab = document.getElementById('statpal-active-tab');
        headerSport.textContent = sportName;
        headerTab.textContent = tabName;

        let finalEndpoint = rawEndpoint;
        const inputs = document.querySelectorAll('.statpal-param-input-val');
        inputs.forEach(el => {
            let val = el.value.trim();
            if(!val) val = 'ari';
            finalEndpoint = finalEndpoint.replace(`{${el.dataset.key}}`, val);
        });

        resultsArea.innerHTML = '';
        overlay.style.display = 'flex';
        contentArea.querySelector('.statpal-pagination-controls').style.display = 'none';

        const params = new URLSearchParams();
        params.append('action', 'statpal_refresh');
        params.append('nonce', StatPalAjax.nonce);
        params.append('sport', sportId);
        params.append('tab', tabId);
        params.append('endpoint', finalEndpoint);

        try {
            const response = await fetch(StatPalAjax.ajaxUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: params.toString()
            });

            const data = await response.json();
            if (data.success && data.data && data.data.html) {
                resultsArea.innerHTML = data.data.html;
                
                paginatedItems = Array.from(resultsArea.querySelectorAll('.statpal-page-item'));
                currentPage = 1;
                handlePagination(contentArea);
            } else {
                resultsArea.innerHTML = `<div class="statpal-empty">No Data Available</div>`;
            }
        } catch (e) {
            resultsArea.innerHTML = `<div class="statpal-empty">No Data Available</div>`;
        } finally {
            overlay.style.display = 'none';
        }
    };

    document.addEventListener('DOMContentLoaded', () => {
        const dashboard = document.querySelector('.statpal-dashboard-container');
        if (!dashboard) return;

        itemsPerPage = parseInt(dashboard.dataset.itemsPerPage) || 10;

        const contentArea = dashboard.querySelector('#statpal-content-area');
        
        dashboard.addEventListener('click', (e) => {
            // Details toggle
            const btnDetails = e.target.closest('.statpal-btn-details');
            if (btnDetails) {
                const card = btnDetails.closest('.statpal-card');
                if(card) {
                    const details = card.querySelector('.statpal-card-details');
                    if(details) {
                        details.style.display = details.style.display === 'none' ? 'block' : 'none';
                    }
                }
                return;
            }

            // Pagination
            const nextBtn = e.target.closest('.statpal-next-page');
            if(nextBtn && !nextBtn.disabled) {
                currentPage++;
                handlePagination(contentArea);
                return;
            }
            const prevBtn = e.target.closest('.statpal-prev-page');
            if(prevBtn && !prevBtn.disabled) {
                currentPage--;
                handlePagination(contentArea);
                return;
            }

            // Sport Expand toggle
            const header = e.target.closest('.statpal-sport-header');
            if (header) {
                const item = header.closest('.statpal-sport-item');
                const isExpanded = item.classList.contains('is-expanded');
                
                dashboard.querySelectorAll('.statpal-sport-item').forEach(i => i.classList.remove('is-expanded'));
                
                if (!isExpanded) {
                    item.classList.add('is-expanded');
                    let targetTab = item.querySelector('.statpal-tab-item[data-tab="odds"]');
                    if (!targetTab) targetTab = item.querySelector('.statpal-tab-item');
                    if (targetTab) targetTab.click();
                }
                return;
            }

            // Tab Click
            const tabBtn = e.target.closest('.statpal-tab-item');
            if (tabBtn) {
                const sportWrap = tabBtn.closest('.statpal-sport-item');
                const sportId = sportWrap.dataset.sport;
                const tabId = tabBtn.dataset.tab;
                const endpoint = tabBtn.dataset.endpoint;
                const paramsStr = tabBtn.dataset.params;

                const isSame = tabBtn.classList.contains('is-active');

                dashboard.querySelectorAll('.statpal-tab-item').forEach(btn => btn.classList.remove('is-active'));
                tabBtn.classList.add('is-active');

                // If tab changed, rebuild the params bar
                if (!isSame) {
                    buildParamsBar(paramsStr, dashboard, () => {
                        fetchData(sportId, tabId, endpoint, contentArea, tabBtn);
                    });
                }
                
                // Fetch data immediately
                fetchData(sportId, tabId, endpoint, contentArea, tabBtn);
                return;
            }

            // Horse Racing Tournament Tabs
            const hrTab = e.target.closest('.statpal-hr-t-tab');
            if (hrTab) {
                const parent = hrTab.closest('.statpal-hr-live-container');
                parent.querySelectorAll('.statpal-hr-t-tab').forEach(t => t.classList.remove('is-active'));
                hrTab.classList.add('is-active');
                
                const targetId = hrTab.getAttribute('data-target');
                parent.querySelectorAll('.statpal-hr-tournament').forEach(tourn => {
                    tourn.style.display = tourn.id === targetId ? 'block' : 'none';
                });
                return;
            }

            // Odds Type Tab Switch (Premium UI)
            const typeTab = e.target.closest('.statpal-odds-type-tab');
            if (typeTab) {
                const container = typeTab.closest('.statpal-odds-premium-container');
                const type = typeTab.dataset.type;
                
                container.querySelectorAll('.statpal-odds-type-tab').forEach(t => t.classList.remove('is-active'));
                typeTab.classList.add('is-active');

                container.querySelectorAll('.statpal-odds-type-content').forEach(content => {
                    content.style.display = content.dataset.typeRef === type ? 'flex' : 'none';
                });
                return;
            }

            // Bookmaker Slider Navigation
            const bmNavBtn = e.target.closest('.statpal-bm-nav');
            if (bmNavBtn) {
                const container = bmNavBtn.closest('.statpal-odds-premium-container');
                const headerSlider = container.querySelector('.statpal-bookmakers-slider');
                const oddsSliders = container.querySelectorAll('.statpal-match-odds-slider');
                
                const sliderContainer = container.querySelector('.statpal-bookmakers-slider-container');
                const containerWidth = sliderContainer.offsetWidth;
                const totalWidth = headerSlider.scrollWidth;
                
                // If content fits, don't scroll
                if (totalWidth <= containerWidth) return;

                const step = 127; // Increased step for better feel
                
                let currentTransform = 0;
                const style = window.getComputedStyle(headerSlider);
                const transformStr = style.transform === 'none' ? 'matrix(1, 0, 0, 1, 0, 0)' : style.transform;
                const matrix = new DOMMatrixReadOnly(transformStr);
                currentTransform = matrix.m41;

                let newTransform = currentTransform;
                if (bmNavBtn.classList.contains('next')) {
                    newTransform -= step;
                } else if (bmNavBtn.classList.contains('prev')) {
                    newTransform += step;
                }

                // Bounds checking
                const maxTransform = -(totalWidth - containerWidth + 20); // 20px buffer

                if (newTransform > 0) newTransform = 0;
                if (newTransform < maxTransform) newTransform = maxTransform;

                headerSlider.style.transform = `translateX(${newTransform}px)`;
                oddsSliders.forEach(s => s.style.transform = `translateX(${newTransform}px)`);
                
                // Toggle visibility of navigation buttons based on position
                const nextBtn = container.querySelector('.statpal-bm-nav.next');
                const prevBtn = container.querySelector('.statpal-bm-nav.prev');
                
                if (prevBtn) {
                    prevBtn.style.display = newTransform < 0 ? 'flex' : 'none';
                } else if (newTransform < 0) {
                    // Create prev button if it doesn't exist
                    const btn = document.createElement('button');
                    btn.type = 'button';
                    btn.className = 'statpal-bm-nav prev';
                    btn.innerHTML = '<span class="dashicons dashicons-arrow-left-alt2"></span>';
                    sliderContainer.appendChild(btn);
                }

                if (nextBtn) {
                    nextBtn.style.display = newTransform <= maxTransform ? 'none' : 'flex';
                }
            }
        });

        // Trigger first load
        let initialTab = dashboard.querySelector('.statpal-sport-item.is-active .statpal-tab-item.is-active') 
                        || dashboard.querySelector('.statpal-tab-item[data-tab="odds"]');
        
        // If neither is found, pick the first tab of the active sport
        if(!initialTab) {
            const activeSport = dashboard.querySelector('.statpal-sport-item.is-active');
            if (activeSport) initialTab = activeSport.querySelector('.statpal-tab-item');
        }

        if(initialTab) {
            initialTab.closest('.statpal-sport-item').classList.add('is-expanded');
            initialTab.click();
        }
    });

})();
