const data = window.greenFundData ?? {};

const openModal = (id) => {
    const modal = document.getElementById(id);
    if (modal) modal.hidden = false;
};

const closeModals = () => {
    document.querySelectorAll('.modal-backdrop').forEach((modal) => {
        modal.hidden = true;
    });
};

document.querySelectorAll('[data-close-modal]').forEach((button) => {
    button.addEventListener('click', closeModals);
});

document.querySelector('[data-open-task]')?.addEventListener('click', () => {
    openModal('task-create-modal');
});
document
    .querySelector('[data-open-maintenance]')
    ?.addEventListener('click', () => openModal('maintenance-create-modal'));
document
    .querySelector('[data-inventory-add]')
    ?.addEventListener('click', () => {
        openModal('asset-create-modal');
    });

const fillMeta = (container, values) => {
    if (!container) return;

    container.replaceChildren(
        ...values.map(([label, value]) => {
            const item = document.createElement('span');
            const heading = document.createElement('b');
            heading.textContent = label;
            item.append(heading, document.createTextNode(String(value ?? '—')));
            return item;
        }),
    );
};

const findById = (records, id) => records?.find((record) => record.id === id);

document.querySelectorAll('[data-open-detail]').forEach((button) => {
    button.addEventListener('click', () => {
        const report = findById(data.reports, button.dataset.openDetail);
        if (!report) return;

        document.getElementById('detail-title').textContent =
            `${report.id} · ${report.category}`;
        document.getElementById('detail-description').textContent =
            report.additionalInformation
                ? `${report.description}\n${report.additionalInformation}`
                : report.description;
        fillMeta(document.getElementById('detail-meta'), [
            ['Location', report.location],
            ['Submitted', report.date],
            ['Priority', report.priority],
            ['Status', report.status],
            ['Assigned to', report.assignee],
        ]);

        const gallery = document.getElementById('report-photo-gallery');
        gallery.replaceChildren();
        report.photos?.forEach((photo) => {
            const image = document.createElement('img');
            image.src = photo.url;
            image.alt = `${photo.type} report evidence`;
            gallery.append(image);
        });

        const reportForm = document.getElementById('report-manage-form');
        if (reportForm) {
            reportForm.action = `/greenfund/reports/${encodeURIComponent(report.id)}`;
            reportForm.elements.status.value = report.statusKey;
            reportForm.elements.priority.value = report.priority.toLowerCase();
            reportForm.elements.assigned_to.value = report.assigneeId ?? '';
        }

        openModal('detail-modal');
    });
});

document
    .querySelector('[data-open-report-manage]')
    ?.addEventListener('click', () => {
        document.getElementById('detail-modal').hidden = true;
        openModal('report-manage-modal');
    });

document.querySelectorAll('[data-task-id]').forEach((button) => {
    button.addEventListener('click', () => {
        const task = findById(data.tasks, button.dataset.taskId);
        if (!task) return;

        document.getElementById('task-dialog-id').textContent =
            `${task.id} · ${task.category}`;
        document.getElementById('task-dialog-title').textContent = task.title;
        document.getElementById('task-dialog-description').textContent =
            task.description ?? task.location;
        fillMeta(document.getElementById('task-dialog-meta'), [
            ['Location', task.location],
            ['Assigned to', task.assignee],
            ['Deadline', task.due],
            ['Priority', task.priority],
            ['Status', task.status],
        ]);

        const mapLink = new URL('/greenfund/map', window.location.origin);
        mapLink.searchParams.set('lat', task.lat);
        mapLink.searchParams.set('lng', task.lng);
        document.getElementById('task-map-link').href = mapLink.toString();

        const progressForm = document.getElementById('task-progress-form');
        const completionForm = document.getElementById('task-completion-form');
        const completed = task.statusKey === 'completed';
        if (progressForm) {
            progressForm.action = `/greenfund/tasks/${encodeURIComponent(task.id)}/progress`;
            progressForm.hidden = completed;
            const statusField = progressForm.elements.status;
            if (statusField.tagName === 'SELECT') {
                statusField.value =
                    task.statusKey === 'in_progress'
                        ? 'in_progress'
                        : 'assigned';
            }
            if (progressForm.elements.assigned_to) {
                progressForm.elements.assigned_to.value = task.assigneeId ?? '';
            }
            if (progressForm.elements.priority) {
                progressForm.elements.priority.value =
                    task.priority.toLowerCase();
            }
            if (progressForm.elements.due_date && task.dueIso) {
                const localDate = new Date(task.dueIso);
                localDate.setMinutes(
                    localDate.getMinutes() - localDate.getTimezoneOffset(),
                );
                progressForm.elements.due_date.value = localDate
                    .toISOString()
                    .slice(0, 16);
            }
        }
        if (completionForm) {
            completionForm.action = `/greenfund/tasks/${encodeURIComponent(task.id)}/complete`;
            completionForm.hidden = completed;
        }

        openModal('task-modal');
    });
});

document.querySelectorAll('[data-asset-id]').forEach((button) => {
    button.addEventListener('click', () => {
        const asset = findById(data.assets, button.dataset.assetId);
        if (!asset) return;

        document.getElementById('asset-dialog-id').textContent =
            `${asset.id} · ${asset.type}`;
        document.getElementById('asset-dialog-title').textContent = asset.name;
        fillMeta(document.getElementById('asset-dialog-meta'), [
            ['Location', asset.location],
            ['Species', asset.species ?? '—'],
            ['Condition', asset.condition],
            ['Registered', asset.date],
        ]);

        const gallery = document.getElementById('asset-photo-gallery');
        gallery.replaceChildren();
        asset.photos?.forEach((url) => {
            const image = document.createElement('img');
            image.src = url;
            image.alt = `${asset.name} green asset`;
            gallery.append(image);
        });

        const history = document.getElementById('asset-history');
        history.replaceChildren();
        (asset.history ?? []).forEach((entry) => {
            const item = document.createElement('div');
            const marker = document.createElement('i');
            const content = document.createElement('span');
            const title = document.createElement('strong');
            const detail = document.createElement('small');
            title.textContent = entry.condition;
            detail.textContent = `${entry.date} · ${entry.updatedBy}${entry.notes ? ` · ${entry.notes}` : ''}`;
            content.append(title, detail);
            item.append(marker, content);
            history.append(item);
        });
        (asset.maintenance ?? []).forEach((record) => {
            const item = document.createElement('div');
            const marker = document.createElement('i');
            const content = document.createElement('span');
            const title = document.createElement('strong');
            const detail = document.createElement('small');
            title.textContent = record.title;
            detail.textContent = `${record.date} · ${record.status} · ${record.assignee}`;
            content.append(title, detail);
            item.append(marker, content);
            history.append(item);
        });
        if (!history.childElementCount) {
            const empty = document.createElement('p');
            empty.textContent =
                'No condition changes or maintenance records yet.';
            history.append(empty);
        }

        const updateForm = document.getElementById('asset-update-form');
        const photosForm = document.getElementById('asset-photos-form');
        if (updateForm) {
            updateForm.action = `/greenfund/assets/${encodeURIComponent(asset.id)}`;
            updateForm.elements.condition.value = asset.conditionKey;
            updateForm.elements.address.value = asset.location;
            updateForm.elements.species.value = asset.species ?? '';
            updateForm.elements.description.value = asset.description ?? '';
            updateForm.elements.history_notes.value = '';
        }
        if (photosForm) {
            photosForm.action = `/greenfund/assets/${encodeURIComponent(asset.id)}/photos`;
        }

        openModal('asset-modal');
    });
});

document
    .querySelectorAll(
        '[data-open-task], [data-open-maintenance], [data-inventory-add]',
    )
    .forEach((button) => {
        button.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') closeModals();
        });
    });

const mobileMenuButton = document.getElementById('mobile-menu-button');
mobileMenuButton?.addEventListener('click', () => {
    const sidebar = document.getElementById('app-sidebar');
    const isOpen = sidebar.classList.toggle('is-open');
    mobileMenuButton.setAttribute('aria-expanded', String(isOpen));
});

const loadScript = (source) =>
    new Promise((resolve, reject) => {
        const script = document.createElement('script');
        script.src = source;
        script.crossOrigin = 'anonymous';
        script.onload = resolve;
        script.onerror = () =>
            reject(new Error('The map library could not be loaded.'));
        document.head.append(script);
    });

const showMapError = (error) => {
    document
        .querySelectorAll('.environment-map, .submission-map')
        .forEach((container) => {
            const message = document.createElement('div');
            message.className = 'map-error';
            message.setAttribute('role', 'alert');
            message.textContent = `${error.message} Check your connection, or continue with the selected city center.`;
            container.replaceChildren(message);
        });
};

const initializeMap = async () => {
    const environmentMap = document.getElementById('environment-map');
    const submissionMap = document.getElementById('submission-map');
    if (!environmentMap && !submissionMap) return;

    try {
        if (!window.L) {
            await loadScript('https://unpkg.com/leaflet@1.9.4/dist/leaflet.js');
        }

        if (environmentMap) {
            if (!window.L.markerClusterGroup) {
                await loadScript(
                    'https://unpkg.com/leaflet.markercluster@1.5.3/dist/leaflet.markercluster.js',
                );
            }

            const map = window.L.map(environmentMap, {
                zoomControl: false,
            }).setView([44.8528, 65.5092], 14);
            const query = new URLSearchParams(window.location.search);
            const requestedLat = Number(query.get('lat'));
            const requestedLng = Number(query.get('lng'));
            if (
                query.has('lat') &&
                query.has('lng') &&
                Number.isFinite(requestedLat) &&
                Number.isFinite(requestedLng)
            ) {
                map.setView([requestedLat, requestedLng], 16);
            }
            window.L.control.zoom({ position: 'bottomright' }).addTo(map);
            window.L.tileLayer(
                'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
                {
                    maxZoom: 19,
                    attribution: '&copy; OpenStreetMap contributors',
                },
            ).addTo(map);

            const cluster = window.L.markerClusterGroup
                ? window.L.markerClusterGroup()
                : window.L.layerGroup();
            const pins = new Map();
            const reportColor = (category) => {
                if (['damaged_tree', 'green_space'].includes(category))
                    return '#16a16c';
                if (['air_pollution', 'water_pollution'].includes(category))
                    return '#8b74d8';
                return '#e5a13c';
            };

            (data.mapReports ?? []).forEach((report) => {
                const marker = window.L.circleMarker([report.lat, report.lng], {
                    radius: 8,
                    color: '#fff',
                    weight: 2,
                    fillColor: reportColor(report.categoryKey),
                    fillOpacity: 1,
                });
                const popup = document.createElement('div');
                const title = document.createElement('strong');
                const location = document.createElement('div');
                const status = document.createElement('span');
                title.textContent = `${report.id} · ${report.category}`;
                location.textContent = report.location;
                status.textContent = report.status;
                popup.append(title, location, status);
                marker.bindPopup(popup);
                pins.set(`report:${report.id}`, {
                    marker,
                    record: report,
                    kind: 'report',
                });
            });

            (data.mapAssets ?? []).forEach((asset) => {
                const marker = window.L.circleMarker([asset.lat, asset.lng], {
                    radius: 7,
                    color: '#fff',
                    weight: 2,
                    fillColor: '#27834d',
                    fillOpacity: 0.95,
                });
                const popup = document.createElement('div');
                const title = document.createElement('strong');
                const location = document.createElement('div');
                const condition = document.createElement('span');
                title.textContent = `${asset.id} · ${asset.name}`;
                location.textContent = asset.location;
                condition.textContent = asset.condition;
                popup.append(title, location, condition);
                marker.bindPopup(popup);
                pins.set(`asset:${asset.id}`, {
                    marker,
                    record: asset,
                    kind: 'asset',
                });
            });

            const refreshMap = () => {
                const category = document.getElementById('map-category').value;
                const status = document.getElementById('map-status').value;
                const term = document
                    .getElementById('map-search')
                    .value.trim()
                    .toLowerCase();
                let reportCount = 0;
                let markerCount = 0;

                pins.forEach(({ marker, record, kind }) => {
                    const categoryMatches =
                        !category ||
                        (category === 'green_assets'
                            ? kind === 'asset'
                            : kind === 'report' &&
                              record.categoryKey === category);
                    const statusMatches =
                        !status ||
                        (kind === 'report' && record.statusKey === status);
                    const searchMatches =
                        !term ||
                        `${record.id} ${record.name ?? ''} ${record.category ?? ''} ${record.location ?? ''}`
                            .toLowerCase()
                            .includes(term);
                    const visible =
                        categoryMatches && statusMatches && searchMatches;

                    if (visible && !cluster.hasLayer(marker))
                        cluster.addLayer(marker);
                    if (!visible && cluster.hasLayer(marker))
                        cluster.removeLayer(marker);
                    if (visible) {
                        markerCount++;
                        if (kind === 'report') reportCount++;
                    }
                });

                document.getElementById('map-result-count').textContent =
                    markerCount;
                document.getElementById('map-nearby-total').textContent =
                    `${reportCount} report${reportCount === 1 ? '' : 's'}`;
                document
                    .querySelectorAll('[data-report-id]')
                    .forEach((card) => {
                        const report = data.mapReports?.find(
                            (item) => item.id === card.dataset.reportId,
                        );
                        const visible =
                            report &&
                            (!category || category === report.categoryKey) &&
                            (!status || status === report.statusKey) &&
                            (!term ||
                                `${report.id} ${report.category} ${report.location}`
                                    .toLowerCase()
                                    .includes(term));
                        card.hidden = !visible;
                    });
            };

            cluster.addTo(map);
            refreshMap();
            ['map-category', 'map-status', 'map-search'].forEach((id) => {
                document
                    .getElementById(id)
                    .addEventListener('input', refreshMap);
            });
            document
                .getElementById('map-reset')
                .addEventListener('click', () => {
                    document.getElementById('map-category').value = '';
                    document.getElementById('map-status').value = '';
                    document.getElementById('map-search').value = '';
                    refreshMap();
                });
            document
                .getElementById('map-locate')
                .addEventListener('click', () => {
                    map.setView([44.8528, 65.5092], 14);
                });
            document.querySelectorAll('[data-report-id]').forEach((card) => {
                card.addEventListener('click', () => {
                    const pin = pins.get(`report:${card.dataset.reportId}`);
                    if (!pin) return;
                    map.setView(pin.marker.getLatLng(), 16);
                    pin.marker.openPopup();
                });
            });
        }

        if (submissionMap) {
            const map = window.L.map(submissionMap, {
                zoomControl: false,
            }).setView([44.8528, 65.5092], 14);
            window.L.tileLayer(
                'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
                {
                    maxZoom: 19,
                    attribution: '&copy; OpenStreetMap contributors',
                },
            ).addTo(map);
            const marker = window.L.marker([44.8528, 65.5092], {
                draggable: true,
            }).addTo(map);
            const setLocation = (latlng) => {
                marker.setLatLng(latlng);
                const latitude = document.getElementById('report-latitude');
                const longitude = document.getElementById('report-longitude');
                const label = document.getElementById('report-location-label');
                latitude.value = latlng.lat.toFixed(6);
                longitude.value = latlng.lng.toFixed(6);
                label.value = `${latlng.lat.toFixed(4)}, ${latlng.lng.toFixed(4)} · Kyzylorda`;
                [latitude, longitude, label].forEach((input) =>
                    input.dispatchEvent(new Event('input', { bubbles: true })),
                );
                document.getElementById('selected-location').textContent =
                    label.value;
            };
            map.on('click', (event) => setLocation(event.latlng));
            marker.on('dragend', () => setLocation(marker.getLatLng()));
            document
                .getElementById('clear-location')
                .addEventListener('click', () => {
                    const center = window.L.latLng(44.8528, 65.5092);
                    map.setView(center, 14);
                    setLocation(center);
                });
            document
                .getElementById('use-my-location')
                .addEventListener('click', () => {
                    if (!navigator.geolocation) {
                        window.alert(
                            'Location services are not available in this browser.',
                        );
                        return;
                    }
                    navigator.geolocation.getCurrentPosition(
                        (position) => {
                            const location = window.L.latLng(
                                position.coords.latitude,
                                position.coords.longitude,
                            );
                            setLocation(location);
                            map.setView(location, 16);
                        },
                        () =>
                            window.alert(
                                'Location access was not granted. You can pin the map manually.',
                            ),
                    );
                });
        }
    } catch (error) {
        showMapError(
            error instanceof Error
                ? error
                : new Error('The map could not be loaded.'),
        );
    }
};

void initializeMap();

const initializeCharts = async () => {
    const canvases = document.querySelectorAll('canvas[id$="-chart"]');
    if (!canvases.length) return;

    const { default: Chart } = await import('chart.js/auto');
    const charts = data.charts ?? {};
    const categoryLabels = Object.keys(charts.reportsByCategory ?? {});
    const categoryNames = categoryLabels.map((category) =>
        category
            .replaceAll('_', ' ')
            .replace(/\b\w/g, (letter) => letter.toUpperCase()),
    );
    const colors = [
        '#dca143',
        '#268453',
        '#8b74d8',
        '#4b98c3',
        '#65b993',
        '#9aa59a',
    ];

    const activityCanvas = document.getElementById('activity-chart');
    if (activityCanvas) {
        new Chart(activityCanvas, {
            type: 'line',
            data: {
                labels: charts.monthlyTrend?.labels ?? [],
                datasets: [
                    {
                        label: 'Reports received',
                        data: charts.monthlyTrend?.received ?? [],
                        borderColor: '#15966b',
                        backgroundColor: 'rgba(16, 185, 129, 0.12)',
                        fill: true,
                        tension: 0.35,
                    },
                    {
                        label: 'Reports resolved',
                        data: charts.monthlyTrend?.resolved ?? [],
                        borderColor: '#7ac9a7',
                        backgroundColor: 'transparent',
                        borderDash: [5, 5],
                        fill: false,
                        tension: 0.35,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom' } },
                scales: { y: { beginAtZero: true, ticks: { precision: 0 } } },
            },
        });
    }

    const categoryCanvas = document.getElementById('category-chart');
    if (categoryCanvas) {
        new Chart(categoryCanvas, {
            type: 'doughnut',
            data: {
                labels: categoryNames,
                datasets: [
                    {
                        data: categoryLabels.map(
                            (category) => charts.reportsByCategory[category],
                        ),
                        backgroundColor: colors,
                        borderWidth: 0,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom' } },
            },
        });
    }

    const conditionCanvas = document.getElementById('condition-chart');
    if (conditionCanvas) {
        const conditions = Object.keys(charts.assetConditions ?? {});
        new Chart(conditionCanvas, {
            type: 'bar',
            data: {
                labels: conditions.map((condition) =>
                    condition
                        .replaceAll('_', ' ')
                        .replace(/\b\w/g, (letter) => letter.toUpperCase()),
                ),
                datasets: [
                    {
                        label: 'Registered assets',
                        data: conditions.map(
                            (condition) => charts.assetConditions[condition],
                        ),
                        backgroundColor: colors,
                        borderRadius: 5,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true, ticks: { precision: 0 } } },
            },
        });
    }

    const taskCanvas = document.getElementById('task-chart');
    if (taskCanvas) {
        const tasks = charts.taskMetrics ?? {};
        new Chart(taskCanvas, {
            type: 'doughnut',
            data: {
                labels: ['Completed', 'Overdue', 'Active'],
                datasets: [
                    {
                        data: [
                            tasks.completed ?? 0,
                            tasks.overdue ?? 0,
                            Math.max(
                                (tasks.total ?? 0) -
                                    (tasks.completed ?? 0) -
                                    (tasks.overdue ?? 0),
                                0,
                            ),
                        ],
                        backgroundColor: ['#268453', '#df7662', '#e7b45b'],
                        borderWidth: 0,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom' } },
            },
        });
    }
};

initializeCharts().catch((error) => {
    console.error('Unable to render analytics charts.', error);
});
