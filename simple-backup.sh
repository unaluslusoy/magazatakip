#!/bin/bash

# Basit Backup ve Timeline Sistemi
# Shell komutları kısıtlı ortamlar için

# Renkli output için
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
PURPLE='\033[0;35m'
CYAN='\033[0;36m'
NC='\033[0m' # No Color

echo -e "${BLUE}📋 Basit Timeline Manager${NC}"
echo -e "${CYAN}=========================${NC}"

case "$1" in
    "create" | "c")
        echo -e "${GREEN}📦 Yeni checkpoint oluşturuluyor...${NC}"
        if [ -n "$2" ]; then
            php scripts/simple-timeline.php create "$2"
        else
            read -p "Checkpoint açıklaması: " description
            php scripts/simple-timeline.php create "$description"
        fi
        ;;
        
    "list" | "l")
        echo -e "${CYAN}📋 Timeline listesi:${NC}"
        php scripts/simple-timeline.php list
        ;;
        
    "rollback" | "r")
        if [ -n "$2" ]; then
            echo -e "${RED}🔄 Rollback işaretleniyor...${NC}"
            php scripts/simple-timeline.php rollback "$2"
        else
            echo -e "${YELLOW}📋 Mevcut checkpoint'ler:${NC}"
            php scripts/simple-timeline.php list
            echo ""
            read -p "Rollback yapmak istediğiniz checkpoint ID: " checkpoint_id
            if [ -n "$checkpoint_id" ]; then
                php scripts/simple-timeline.php rollback "$checkpoint_id"
            else
                echo -e "${RED}❌ Checkpoint ID belirtilmedi.${NC}"
            fi
        fi
        ;;
        
    "status" | "s")
        echo -e "${CYAN}📊 Sistem durumu:${NC}"
        echo ""
        echo -e "${GREEN}📅 Timeline özeti:${NC}"
        php scripts/simple-timeline.php list | head -10
        echo ""
        echo -e "${BLUE}📁 Dizin boyutları:${NC}"
        du -sh . 2>/dev/null | head -1
        echo ""
        echo -e "${PURPLE}📈 Son değişiklikler:${NC}"
        ls -la | head -5
        ;;
        
    "auto")
        case "$2" in
            "update")
                echo -e "${YELLOW}🔄 Güncelleme öncesi checkpoint...${NC}"
                php scripts/simple-timeline.php create "🔄 Güncelleme öncesi - $3"
                ;;
            "config")
                echo -e "${PURPLE}⚙️ Config değişikliği öncesi checkpoint...${NC}"
                php scripts/simple-timeline.php create "⚙️ Config değişikliği - $3"
                ;;
            "critical")
                echo -e "${RED}🚨 Kritik işlem öncesi checkpoint...${NC}"
                php scripts/simple-timeline.php create "🚨 Kritik işlem öncesi - $3"
                ;;
            *)
                echo -e "${RED}❌ Geçersiz auto komut: $2${NC}"
                echo "Kullanım: ./simple-backup.sh auto [update|config|critical] [açıklama]"
                ;;
        esac
        ;;
        
    "emergency" | "em")
        echo -e "${RED}🚨 ACİL DURUM TİMELİNE${NC}"
        echo -e "${RED}=====================${NC}"
        echo ""
        echo -e "${YELLOW}Son 5 checkpoint:${NC}"
        php scripts/simple-timeline.php list | head -6
        echo ""
        echo -e "${RED}⚠️  Manuel rollback gerekli!${NC}"
        echo -e "${CYAN}1. Git commit hash'lerini kontrol edin${NC}"
        echo -e "${CYAN}2. Veritabanı backup'larını kontrol edin${NC}"
        echo -e "${CYAN}3. Manuel olarak geri alın${NC}"
        ;;
        
    "help" | "h" | "" | *)
        echo -e "${GREEN}📖 Basit Timeline Kılavuzu:${NC}"
        echo ""
        echo -e "${CYAN}Temel Komutlar:${NC}"
        echo "  ./simple-backup.sh create [açıklama]    - Yeni checkpoint"
        echo "  ./simple-backup.sh list                 - Timeline listele"
        echo "  ./simple-backup.sh rollback [id]        - Rollback işaretle"
        echo ""
        echo -e "${CYAN}Durum Kontrolü:${NC}"
        echo "  ./simple-backup.sh status               - Sistem durumu"
        echo "  ./simple-backup.sh emergency            - Acil durum menüsü"
        echo ""
        echo -e "${CYAN}Otomatik Checkpoint'ler:${NC}"
        echo "  ./simple-backup.sh auto update          - Güncelleme öncesi"
        echo "  ./simple-backup.sh auto config          - Config değişikliği"
        echo "  ./simple-backup.sh auto critical        - Kritik işlem öncesi"
        echo ""
        echo -e "${CYAN}Kısa Komutlar:${NC}"
        echo "  c = create, l = list, r = rollback"
        echo "  s = status, em = emergency"
        echo ""
        echo -e "${YELLOW}💡 Bu basit versiyon sadece checkpoint kaydı tutar.${NC}"
        echo -e "${YELLOW}   Gerçek rollback işlemleri manuel yapılmalıdır.${NC}"
        echo ""
        echo -e "${CYAN}🔗 Web arayüzü: https://magazatakip.com.tr/admin/timeline${NC}"
        ;;
esac

echo ""