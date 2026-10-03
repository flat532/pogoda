#!/bin/bash

# Tablica z listą katalogów, które mają być przetwarzane
BASE_DIRS=(
    "/home/srv55800/domains/flat532.pl/public_html/dubaj/archive"
    "/home/srv55800/domains/drugapredkosc.pl/public_html/archive"
)

for ARCHIVE_DIR in "${BASE_DIRS[@]}"; do
    OLD_DIR="$ARCHIVE_DIR/old"
    ARCHIVE_FILE="old.tar"

    # Upewnij się, że katalog OLD istnieje
    if [ ! -d "$OLD_DIR" ]; then
        mkdir -p "$OLD_DIR"
    fi

    # Przeniesienie plików starszych niż 24 godziny (1440 minut) do katalogu /archive/old
    find "$ARCHIVE_DIR" -type f -mmin +1440 -not -path "$OLD_DIR/*" -exec mv -t "$OLD_DIR" {} +

    # Lista plików do zarchiwizowania (wszystko w OLD_DIR poza samym archiwum)
    FILES=$(find "$OLD_DIR" -maxdepth 1 -type f ! -name "$ARCHIVE_FILE" -printf '%f\n')

    if [ -z "$FILES" ]; then
        echo "Brak plików starszych niż 24 godziny w $ARCHIVE_DIR"
        continue
    fi

    # Jedno wywołanie tar dla całej paczki (--append tworzy archiwum, jeśli nie istnieje);
    # pliki usuwamy tylko po udanym dopisaniu
    if printf '%s\n' "$FILES" | tar --append -f "$OLD_DIR/$ARCHIVE_FILE" -C "$OLD_DIR" -T -; then
        printf '%s\n' "$FILES" | (cd "$OLD_DIR" && xargs rm -f --)
    else
        echo "BŁĄD: nie udało się dopisać plików do $OLD_DIR/$ARCHIVE_FILE" >&2
    fi
done
