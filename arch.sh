#!/bin/bash

# Tablica z listą katalogów, które mają być przetwarzane
BASE_DIRS=(
    "/home/srv55800/domains/flat532.pl/public_html/dubaj/archive"
    "/home/srv55800/domains/drugapredkosc.pl/public_htmlwq/archive"
)

for ARCHIVE_DIR in "${BASE_DIRS[@]}"; do
    OLD_DIR="$ARCHIVE_DIR/old"
    ARCHIVE_FILE="old.tar"

    # Upewnij się, że katalog OLD istnieje
    if [ ! -d "$OLD_DIR" ]; then
        mkdir -p "$OLD_DIR"
    fi

    # Znalezienie plików starszych niż 24 godziny (1440 minut) i zliczenie ich, wykluczając katalog docelowy
    file_count=$(find "$ARCHIVE_DIR" -type f -mmin +1440 -not -path "$OLD_DIR/*" | wc -l)

    if [ $file_count -eq 0 ]; then
        echo "Brak plików starszych niż 24 godziny w $ARCHIVE_DIR"
    else
        # Przeniesienie plików starszych niż 24 godziny do katalogu /archive/old i wypisanie komunikatu
        find "$ARCHIVE_DIR" -type f -mmin +1440 -not -path "$OLD_DIR/*" -exec mv "{}" "$OLD_DIR" \;

        # Sprawdzenie, czy plik old.tar istnieje
        if [ -f "$OLD_DIR/$ARCHIVE_FILE" ]; then
            # Jeśli istnieje, dodaj pliki do archiwum, wykluczając plik old.tar
            find "$OLD_DIR" -type f ! -name "$ARCHIVE_FILE" -exec tar --append -f "$OLD_DIR/$ARCHIVE_FILE" "{}" \; -exec rm "{}" \;
        else
            # Jeśli nie istnieje, utwórz nowe archiwum, wykluczając plik old.tar
            tar -cvf "$OLD_DIR/$ARCHIVE_FILE" -C "$OLD_DIR" --exclude "$ARCHIVE_FILE" .
            find "$OLD_DIR" -type f ! -name "$ARCHIVE_FILE" -exec rm "{}" \;
        fi
    fi
done
