// Tłumaczenia interfejsu. Nowy język = nowy klucz w I18N z kompletem tekstów
// (klucze jak w 'pl'); przycisk w przełączniku pojawi się automatycznie.
const I18N = {
    pl: {
        _name: 'PL',
        _label: 'Polski',
        _locale: 'pl-PL',

        pageTitle: 'Stacja Pogodowa {city}',
        brand: 'Stacja Pogodowa',
        brandSub: '{city} · na żywo',
        langSwitch: 'Język',
        citySwitch: 'Miasto',

        // Odmienione nazwy miast („w …”, „dla …”); brak wpisu → inCity / forCity
        cities: {
            Gliwice: { in: 'w Gliwicach', for: 'dla Gliwic' },
            Amsterdam: { in: 'w Amsterdamie', for: 'dla Amsterdamu' }
        },
        inCity: 'w {city}',
        forCity: 'dla {city}',
        collectingSince: 'Dane {cityFor} zbierane są od {date}. Rekordy i statystyki będą się uzupełniać z czasem.',

        nowIn: 'Teraz {cityIn}',
        lastReading: 'Ostatni pomiar: {time}',
        humidity: 'Wilgotność',
        pressure: 'Ciśnienie',
        temperature: 'Temperatura',
        wind: 'Wiatr',
        precipitation: 'Opady',

        histSame: 'Rok temu o tej godzinie temperatura była <strong>taka sama</strong> ({temp}°C).',
        histColder: 'Rok temu o tej godzinie było <strong>chłodniej o {diff}°C</strong> (temp: {temp}°C).',
        histWarmer: 'Rok temu o tej godzinie było <strong>cieplej o {diff}°C</strong> (temp: {temp}°C).',

        selectedDay: 'Wybrany dzień',
        prevDay: 'Poprzedni dzień',
        nextDay: 'Następny dzień',
        show: 'Pokaż',
        maxTemp: 'Max temp',
        minTemp: 'Min temp',

        records: 'Rekordy',
        monthlyOverview: 'Przegląd miesięcy',
        month: 'Miesiąc',
        yearlyTrend: 'Trend roczny (min / max)',
        last12Months: 'Ostatnie 12 miesięcy',
        avgTemperature: 'Średnia temperatura',
        rangeToday: 'Dziś',
        range7days: '7 dni',
        range30days: '30 dni',
        rangeMonth: 'Ten msc',
        rangeYear: 'Ten rok',

        chartMaxTemp: 'Max temp (°C)',
        chartMinTemp: 'Min temp (°C)',
        chartAvgTemp: 'Średnia temp (°C)',

        dstSummer: 'Do zmiany na czas letni:',
        dstWinter: 'Do zmiany na czas zimowy:',
        days: { one: 'dzień', few: 'dni', many: 'dni', other: 'dni' },
        hoursShort: 'godz',
        minutesShort: 'min',

        footer: 'Stacja Pogodowa {city} · dane: OpenWeatherMap · odczyt automatyczny co godzinę'
    },

    en: {
        _name: 'EN',
        _label: 'English',
        _locale: 'en-GB',

        pageTitle: '{city} Weather Station',
        brand: 'Weather Station',
        brandSub: '{city} · live',
        langSwitch: 'Language',
        citySwitch: 'City',

        cities: {},
        inCity: 'in {city}',
        forCity: 'for {city}',
        collectingSince: 'Data {cityFor} has been collected since {date}. Records and statistics will fill in over time.',

        nowIn: 'Now {cityIn}',
        lastReading: 'Last reading: {time}',
        humidity: 'Humidity',
        pressure: 'Pressure',
        temperature: 'Temperature',
        wind: 'Wind',
        precipitation: 'Precipitation',

        histSame: 'A year ago at this hour the temperature was <strong>the same</strong> ({temp}°C).',
        histColder: 'A year ago at this hour it was <strong>{diff}°C colder</strong> (temp: {temp}°C).',
        histWarmer: 'A year ago at this hour it was <strong>{diff}°C warmer</strong> (temp: {temp}°C).',

        selectedDay: 'Selected day',
        prevDay: 'Previous day',
        nextDay: 'Next day',
        show: 'Show',
        maxTemp: 'Max temp',
        minTemp: 'Min temp',

        records: 'Records',
        monthlyOverview: 'Monthly overview',
        month: 'Month',
        yearlyTrend: 'Yearly trend (min / max)',
        last12Months: 'Last 12 months',
        avgTemperature: 'Average temperature',
        rangeToday: 'Today',
        range7days: '7 days',
        range30days: '30 days',
        rangeMonth: 'This month',
        rangeYear: 'This year',

        chartMaxTemp: 'Max temp (°C)',
        chartMinTemp: 'Min temp (°C)',
        chartAvgTemp: 'Avg temp (°C)',

        dstSummer: 'Until summer time:',
        dstWinter: 'Until winter time:',
        days: { one: 'day', other: 'days' },
        hoursShort: 'h',
        minutesShort: 'min',

        footer: '{city} Weather Station · data: OpenWeatherMap · updated automatically every hour'
    },

    nl: {
        _name: 'NL',
        _label: 'Nederlands',
        _locale: 'nl-NL',

        pageTitle: 'Weerstation {city}',
        brand: 'Weerstation',
        brandSub: '{city} · live',
        langSwitch: 'Taal',
        citySwitch: 'Stad',

        cities: {},
        inCity: 'in {city}',
        forCity: 'voor {city}',
        collectingSince: 'Gegevens {cityFor} worden verzameld sinds {date}. Records en statistieken vullen zich in de loop van de tijd aan.',

        nowIn: 'Nu {cityIn}',
        lastReading: 'Laatste meting: {time}',
        humidity: 'Luchtvochtigheid',
        pressure: 'Luchtdruk',
        temperature: 'Temperatuur',
        wind: 'Wind',
        precipitation: 'Neerslag',

        histSame: 'Een jaar geleden was de temperatuur op dit uur <strong>hetzelfde</strong> ({temp}°C).',
        histColder: 'Een jaar geleden was het op dit uur <strong>{diff}°C kouder</strong> (temp: {temp}°C).',
        histWarmer: 'Een jaar geleden was het op dit uur <strong>{diff}°C warmer</strong> (temp: {temp}°C).',

        selectedDay: 'Geselecteerde dag',
        prevDay: 'Vorige dag',
        nextDay: 'Volgende dag',
        show: 'Toon',
        maxTemp: 'Max temp',
        minTemp: 'Min temp',

        records: 'Records',
        monthlyOverview: 'Maandoverzicht',
        month: 'Maand',
        yearlyTrend: 'Jaartrend (min / max)',
        last12Months: 'Laatste 12 maanden',
        avgTemperature: 'Gemiddelde temperatuur',
        rangeToday: 'Vandaag',
        range7days: '7 dagen',
        range30days: '30 dagen',
        rangeMonth: 'Deze maand',
        rangeYear: 'Dit jaar',

        chartMaxTemp: 'Max temp (°C)',
        chartMinTemp: 'Min temp (°C)',
        chartAvgTemp: 'Gem. temp (°C)',

        dstSummer: 'Tot de zomertijd:',
        dstWinter: 'Tot de wintertijd:',
        days: { one: 'dag', other: 'dagen' },
        hoursShort: 'u',
        minutesShort: 'min',

        footer: 'Weerstation {city} · gegevens: OpenWeatherMap · elk uur automatisch bijgewerkt'
    }
};

const DEFAULT_LANG = 'pl';
let currentLang = detectLang();

function detectLang() {
    const fromUrl = new URLSearchParams(location.search).get('lang');
    if (fromUrl && I18N[fromUrl]) return fromUrl;

    try {
        const saved = localStorage.getItem('lang');
        if (saved && I18N[saved]) return saved;
    } catch (e) { }

    // Przeglądarka po polsku → PL, każdy inny język → EN (o ile nie ma własnego słownika)
    for (const l of navigator.languages || [navigator.language]) {
        const code = (l || '').slice(0, 2).toLowerCase();
        if (I18N[code]) return code;
    }
    return I18N.en ? 'en' : DEFAULT_LANG;
}

// Bieżące miasto (klucz z bazy) — wstawiane do tekstów jako {city}, {cityIn} i {cityFor}
let i18nCity = null;

function setI18nCity(key) {
    i18nCity = key;
}

function fill(text, params) {
    return text.replace(/\{(\w+)\}/g, (m, name) => params[name] ?? m);
}

// Tekst z bieżącego słownika; {nazwa} zastępowane wartościami z params
function t(key, params = {}) {
    const text = I18N[currentLang][key] ?? I18N[DEFAULT_LANG][key] ?? key;
    const context = i18nCity
        ? { city: cityName(i18nCity), cityIn: cityForm(i18nCity, 'in', 'inCity'), cityFor: cityForm(i18nCity, 'for', 'forCity') }
        : {};
    return fill(text, { ...context, ...params });
}

// Nazwa miasta w bieżącym języku
function cityName(key) {
    return I18N[currentLang].cities?.[key]?.name ?? key;
}

// Odmieniona forma nazwy (np. 'in' → w Gliwicach / in Gliwice); brak wpisu → wzorzec fallbackKey
function cityForm(key, form, fallbackKey) {
    return I18N[currentLang].cities?.[key]?.[form] ?? fill(I18N[currentLang][fallbackKey], { city: cityName(key) });
}

// Forma liczebnika zgodna z regułami języka (np. 1 dzień / 2 dni / 5 dni)
function plural(key, n) {
    const forms = I18N[currentLang][key];
    return forms[new Intl.PluralRules(locale()).select(n)] ?? forms.other;
}

function locale() {
    return I18N[currentLang]._locale;
}

// Teksty statyczne: data-i18n → textContent, data-i18n-aria → aria-label
function applyTranslations() {
    document.documentElement.lang = currentLang;
    document.title = t('pageTitle');
    document.querySelectorAll('[data-i18n]').forEach(el => { el.textContent = t(el.dataset.i18n); });
    document.querySelectorAll('[data-i18n-aria]').forEach(el => { el.setAttribute('aria-label', t(el.dataset.i18nAria)); });
}

function renderLangSwitch(container, onChange) {
    container.setAttribute('aria-label', t('langSwitch'));
    container.innerHTML = '';
    Object.keys(I18N).forEach(code => {
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'nav-link' + (code === currentLang ? ' active' : '');
        btn.textContent = I18N[code]._name;
        btn.title = I18N[code]._label;
        btn.lang = code;
        btn.setAttribute('aria-pressed', code === currentLang);
        btn.addEventListener('click', () => {
            if (code === currentLang) return;
            setLang(code);
            renderLangSwitch(container, onChange);
            onChange();
        });
        container.appendChild(btn);
    });
}

function setLang(code) {
    currentLang = code;
    try { localStorage.setItem('lang', code); } catch (e) { }

    // Link z ?lang= ma pokazywać aktualny wybór
    const url = new URL(location.href);
    if (url.searchParams.has('lang')) {
        url.searchParams.set('lang', code);
        history.replaceState(null, '', url);
    }
    applyTranslations();
}
