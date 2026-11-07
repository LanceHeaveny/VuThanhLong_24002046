<?php
require_once __DIR__ . '/common/functions.php';

$pageTitle = 'Dự báo thời tiết';
require __DIR__ . '/view/site_header.php';
?>
    <section class="forecast-page">
        <div class="forecast-heading">
            <div>
                <p class="eyebrow">DỰ BÁO OPEN-METEO</p>
                <h1>Dự báo thời tiết</h1>
                <p class="heading-subtitle">12 giờ tròn kế tiếp và dự báo 7 ngày tại vị trí bạn chọn.</p>
            </div>
            <div class="forecast-location-controls">
                <label for="forecast-province">Tỉnh/thành phố</label>
                <select id="forecast-province">
                    <option value="">Đang tải tỉnh/thành phố...</option>
                </select>
                <label for="forecast-ward">Xã/phường (không bắt buộc)</label>
                <select id="forecast-ward" disabled>
                    <option value="">Dự báo theo tỉnh/thành phố</option>
                </select>
                <button class="button button-secondary" id="forecast-refresh" type="button">Cập nhật dự báo</button>
            </div>
        </div>

        <p class="forecast-location-label" id="forecast-location-label" aria-live="polite">Đang tải dự báo...</p>
        <p class="forecast-data-status" id="forecast-data-status" aria-live="polite" hidden></p>
        <p class="forecast-error notice notice-error" id="forecast-error" role="alert" hidden></p>

        <section class="forecast-section" aria-labelledby="hourly-heading">
            <div class="forecast-section-heading">
                <div>
                    <p class="eyebrow">TRONG NGÀY</p>
                    <h2 id="hourly-heading">12 giờ kế tiếp</h2>
                </div>
                <span class="forecast-timezone">Giờ Việt Nam</span>
            </div>
            <div class="forecast-hourly-grid" id="forecast-hourly" aria-live="polite">
                <p class="forecast-loading">Đang tải dự báo theo giờ...</p>
            </div>
        </section>

        <section class="forecast-section" aria-labelledby="daily-heading">
            <div class="forecast-section-heading">
                <div>
                    <p class="eyebrow">7 NGÀY</p>
                    <h2 id="daily-heading">Dự báo trong tuần</h2>
                </div>
            </div>
            <div class="forecast-daily-grid" id="forecast-daily" aria-live="polite">
                <p class="forecast-loading">Đang tải dự báo theo ngày...</p>
            </div>
        </section>

        <p class="forecast-attribution">
            Dự báo do <a href="https://open-meteo.com/" target="_blank" rel="noopener noreferrer">Open-Meteo</a> cung cấp.
            Đây là dữ liệu dự báo theo mô hình thời tiết, không phải số đo radar hoặc cảm biến tại chỗ.
        </p>
    </section>
    <script>
        const provinceSelect = document.getElementById('forecast-province');
        const wardSelect = document.getElementById('forecast-ward');
        const hourlyForecast = document.getElementById('forecast-hourly');
        const dailyForecast = document.getElementById('forecast-daily');
        const errorMessage = document.getElementById('forecast-error');
        const locationLabel = document.getElementById('forecast-location-label');
        const dataStatus = document.getElementById('forecast-data-status');
        const wardGroups = new Map();
        let requestSequence = 0;

        function weatherIcon(weatherType) {
            const icons = {
                'trời quang': '☀️',
                'ít mây': '🌤️',
                'mây rải rác': '⛅',
                'nhiều mây': '☁️',
                'sương mù': '🌫️',
                'mưa phùn': '🌦️',
                'mưa': '🌧️',
                'mưa lớn': '🌧️',
                'mưa rào': '🌦️',
                'mưa rào lớn': '🌧️',
                'tuyết': '❄️',
                'dông': '⛈️'
            };
            return icons[weatherType] || '◷';
        }

        function formatNumber(value) {
            return Number(value).toLocaleString('vi-VN', { maximumFractionDigits: 1 });
        }

        function formatHourCard(item) {
            const date = new Date(item.time);
            const card = document.createElement('article');
            card.className = 'forecast-hour-card';
            const time = document.createElement('time');
            time.dateTime = item.time;
            time.textContent = new Intl.DateTimeFormat('vi-VN', {
                timeZone: 'Asia/Ho_Chi_Minh',
                hour: '2-digit',
                minute: '2-digit',
                hour12: false
            }).format(date);
            const icon = document.createElement('span');
            icon.className = 'forecast-weather-icon';
            icon.setAttribute('aria-hidden', 'true');
            icon.textContent = weatherIcon(item.weather_type);
            const condition = document.createElement('span');
            condition.className = 'forecast-condition';
            condition.textContent = item.weather_type;
            const temperature = document.createElement('strong');
            temperature.className = 'forecast-hour-temperature';
            temperature.textContent = `${formatNumber(item.temperature)}°`;
            const details = document.createElement('span');
            details.className = 'forecast-card-detail';
            details.textContent = `Mưa ${item.precipitation_probability ?? '—'}%`;
            const humidity = document.createElement('span');
            humidity.className = 'forecast-card-detail';
            humidity.textContent = `Ẩm ${item.humidity}%`;
            card.append(time, icon, condition, temperature, details, humidity);
            return card;
        }

        function formatDailyCard(item, index) {
            const date = new Date(`${item.date}T12:00:00+07:00`);
            const card = document.createElement('article');
            card.className = 'forecast-day-card';
            const day = document.createElement('strong');
            day.textContent = index === 0
                ? 'Hôm nay'
                : new Intl.DateTimeFormat('vi-VN', {
                    timeZone: 'Asia/Ho_Chi_Minh',
                    weekday: 'long'
                }).format(date);
            const dateLabel = document.createElement('time');
            dateLabel.dateTime = item.date;
            dateLabel.className = 'forecast-date';
            dateLabel.textContent = new Intl.DateTimeFormat('vi-VN', {
                timeZone: 'Asia/Ho_Chi_Minh',
                day: '2-digit',
                month: '2-digit'
            }).format(date);
            const icon = document.createElement('span');
            icon.className = 'forecast-weather-icon';
            icon.setAttribute('aria-hidden', 'true');
            icon.textContent = weatherIcon(item.weather_type);
            const condition = document.createElement('span');
            condition.className = 'forecast-condition';
            condition.textContent = item.weather_type;
            const temperatures = document.createElement('strong');
            temperatures.className = 'forecast-day-temperatures';
            temperatures.textContent = `${formatNumber(item.temperature_max)}° / ${formatNumber(item.temperature_min)}°`;
            const precipitation = document.createElement('span');
            precipitation.className = 'forecast-card-detail';
            precipitation.textContent = `Khả năng mưa ${item.precipitation_probability ?? '—'}%`;
            card.append(day, dateLabel, icon, condition, temperatures, precipitation);
            return card;
        }

        async function loadForecast(locationKey) {
            if (!locationKey) return;
            const currentRequest = ++requestSequence;
            errorMessage.hidden = true;
            dataStatus.hidden = true;
            locationLabel.textContent = 'Đang tải dự báo...';
            hourlyForecast.replaceChildren(Object.assign(document.createElement('p'), {
                className: 'forecast-loading',
                textContent: 'Đang tải dự báo theo giờ...'
            }));
            dailyForecast.replaceChildren(Object.assign(document.createElement('p'), {
                className: 'forecast-loading',
                textContent: 'Đang tải dự báo theo ngày...'
            }));

            try {
                const response = await fetch(`forecast_feed.php?location=${encodeURIComponent(locationKey)}`, { cache: 'no-store' });
                const data = await response.json();
                if (!response.ok) throw new Error(data.error || 'Không thể tải dữ báo thời tiết.');
                if (currentRequest !== requestSequence) return;
                if (!Array.isArray(data.hourly) || data.hourly.length !== 12
                    || !Array.isArray(data.daily) || data.daily.length !== 7
                ) {
                    throw new Error('Dữ liệu dự báo chưa đủ 12 mốc giờ và 7 ngày.');
                }

                hourlyForecast.replaceChildren(...data.hourly.map(formatHourCard));
                dailyForecast.replaceChildren(...data.daily.map(formatDailyCard));
                locationLabel.textContent = data.location.name;
                if (data.source === 'cache') {
                    const cachedAt = data.cached_at
                        ? new Intl.DateTimeFormat('vi-VN', {
                            timeZone: 'Asia/Ho_Chi_Minh',
                            dateStyle: 'short',
                            timeStyle: 'short'
                        }).format(new Date(data.cached_at))
                        : '';
                    dataStatus.textContent = `Open-Meteo đang mất kết nối. Đang hiển thị dự báo đã lưu${cachedAt ? ` lúc ${cachedAt}` : ''}.`;
                    dataStatus.hidden = false;
                } else if (data.cache_warning) {
                    dataStatus.textContent = data.cache_warning;
                    dataStatus.hidden = false;
                }
            } catch (error) {
                if (currentRequest !== requestSequence) return;
                dataStatus.hidden = true;
                errorMessage.textContent = error instanceof Error ? error.message : 'Không thể tải dự báo thời tiết.';
                errorMessage.hidden = false;
                locationLabel.textContent = 'Chưa có dữ liệu dự báo.';
                hourlyForecast.replaceChildren();
                dailyForecast.replaceChildren();
            }
        }

        async function initializeForecast() {
            try {
                const [locationsResponse, wardsResponse] = await Promise.all([
                    fetch('dashboard_feed.php?action=locations', { cache: 'no-store' }),
                    fetch('dashboard_feed.php?action=wards', { cache: 'no-store' })
                ]);
                const locationsData = await locationsResponse.json();
                const wardsData = await wardsResponse.json();
                if (!locationsResponse.ok || !Array.isArray(locationsData.locations)) {
                    throw new Error(locationsData.error || 'Không thể tải danh sách tỉnh/thành phố.');
                }
                if (!wardsResponse.ok || !Array.isArray(wardsData.wards)) {
                    throw new Error(wardsData.error || 'Không thể tải danh sách xã/phường.');
                }

                const provincePlaceholder = document.createElement('option');
                provincePlaceholder.value = '';
                provincePlaceholder.textContent = 'Chọn tỉnh/thành phố';
                provinceSelect.replaceChildren(provincePlaceholder);

                locationsData.locations.forEach((location) => {
                    const option = document.createElement('option');
                    option.value = location.key;
                    option.textContent = location.name;
                    provinceSelect.append(option);
                });

                wardsData.wards.forEach((ward) => {
                    if (!wardGroups.has(ward.province_key)) {
                        const group = document.createElement('optgroup');
                        group.label = locationsData.locations.find(
                            (location) => location.key === ward.province_key
                        )?.name || '';
                        wardGroups.set(ward.province_key, group);
                    }
                    const option = document.createElement('option');
                    option.value = ward.key;
                    option.textContent = ward.name;
                    wardGroups.get(ward.province_key).append(option);
                });

                const preferredWard = wardsData.wards.find(
                    (ward) => ward.key === locationsData.preferred_location_key
                );
                let selectedWardKey = '';
                if (preferredWard) {
                    provinceSelect.value = preferredWard.province_key;
                    selectedWardKey = preferredWard.key;
                } else if (locationsData.locations.some(
                    (location) => location.key === locationsData.preferred_location_key
                )) {
                    provinceSelect.value = locationsData.preferred_location_key;
                } else if (locationsData.locations.length > 0) {
                    provinceSelect.value = locationsData.locations[0].key;
                }

                showWardsForProvince(provinceSelect.value, selectedWardKey);
                await loadForecast(wardSelect.value || provinceSelect.value);
            } catch (error) {
                errorMessage.textContent = error instanceof Error ? error.message : 'Không thể khởi tạo trang dự báo.';
                errorMessage.hidden = false;
                locationLabel.textContent = 'Chưa có dữ liệu dự báo.';
            }
        }

        function showWardsForProvince(provinceKey, selectedWardKey = '') {
            const placeholder = document.createElement('option');
            placeholder.value = '';
            placeholder.textContent = provinceKey ? 'Dự báo theo tỉnh/thành phố' : 'Chọn tỉnh/thành phố trước';
            wardSelect.replaceChildren(placeholder);
            const group = wardGroups.get(provinceKey);
            if (!group) {
                wardSelect.disabled = true;
                return;
            }
            wardSelect.append(group.cloneNode(true));
            wardSelect.disabled = false;
            wardSelect.value = selectedWardKey;
        }

        provinceSelect.addEventListener('change', () => {
            showWardsForProvince(provinceSelect.value);
            loadForecast(provinceSelect.value);
        });
        wardSelect.addEventListener('change', () => {
            loadForecast(wardSelect.value || provinceSelect.value);
        });
        document.getElementById('forecast-refresh').addEventListener('click', () => {
            loadForecast(wardSelect.value || provinceSelect.value);
        });

        initializeForecast();
    </script>
<?php require __DIR__ . '/view/site_footer.php'; ?>
