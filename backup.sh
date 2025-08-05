#!/bin/bash

# Hızlı Backup ve Rollback Komutları
# Kullanım: ./backup.sh [komut] [parametreler]

# Renkli output için
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
PURPLE='\033[0;35m'
CYAN='\033[0;36m'
NC='\033[0m' # No Color

echo -e "${BLUE}🕐 Magazatakip Timeline Manager${NC}"
echo -e "${CYAN}================================${NC}"

case "$1" in
    "create" | "c")
        echo -e "${GREEN}📦 Yeni checkpoint oluşturuluyor...${NC}"
        if [ -n "$2" ]; then
            php scripts/timeline-manager.php create "$2"
        else
            read -p "Checkpoint açıklaması: " description
            php scripts/timeline-manager.php create "$description"
        fi
        ;;
        
    "list" | "l")
        echo -e "${CYAN}📋 Timeline listesi:${NC}"
        php scripts/timeline-manager.php list
        ;;
        
    "rollback" | "r")
        if [ -n "$2" ]; then
            echo -e "${RED}🔄 Rollback başlatılıyor...${NC}"
            php scripts/timeline-manager.php rollback "$2"
        else
            echo -e "${YELLOW}⚡ Hızlı rollback menüsü:${NC}"
            php scripts/quick-rollback.php
        fi
        ;;
        
    "daily" | "d")
        echo -e "${GREEN}📅 Günlük backup oluşturuluyor...${NC}"
        php scripts/auto-checkpoint.php daily
        ;;
        
    "weekly" | "w")
        echo -e "${PURPLE}📆 Haftalık backup oluşturuluyor...${NC}"
        php scripts/auto-checkpoint.php weekly
        ;;
        
    "cleanup" | "clean")
        echo -e "${YELLOW}🧹 Eski backup'lar temizleniyor...${NC}"
        days=${2:-7}
        php scripts/timeline-manager.php cleanup $days
        ;;
        
    "emergency" | "em")
        echo -e "${RED}🚨 ACİL ROLLBACK MODu${NC}"
        echo -e "${RED}Bu mod sadece kritik hatalar için kullanılmalıdır!${NC}"
        echo ""
        read -p "Devam etmek istiyor musunuz? (evet/hayır): " confirm
        if [ "$confirm" = "evet" ]; then
            php scripts/quick-rollback.php
        else
            echo -e "${YELLOW}❌ İşlem iptal edildi.${NC}"
        fi
        ;;
        
    "status" | "s")
        echo -e "${CYAN}📊 Sistem durumu:${NC}"
        echo ""
        echo -e "${GREEN}📦 Git durumu:${NC}"
        git status --short
        echo ""
        echo -e "${BLUE}📅 Son checkpoint:${NC}"
        php scripts/timeline-manager.php list | head -5
        echo ""
        echo -e "${PURPLE}💾 Backup boyutları:${NC}"
        if [ -d "backups" ]; then
            du -sh backups/* 2>/dev/null | tail -5
        else
            echo "Henüz backup oluşturulmamış"
        fi
        ;;
        
    "auto")
        case "$2" in
            "update")
                echo -e "${YELLOW}🔄 Güncelleme öncesi checkpoint...${NC}"
                php scripts/auto-checkpoint.php update "$3"
                ;;
            "migration")
                echo -e "${BLUE}🗄️ Migration öncesi checkpoint...${NC}"
                php scripts/auto-checkpoint.php migration "$3"
                ;;
            "config")
                echo -e "${PURPLE}⚙️ Config değişikliği öncesi checkpoint...${NC}"
                php scripts/auto-checkpoint.php config "$3"
                ;;
            *)
                echo -e "${RED}❌ Geçersiz auto komut: $2${NC}"
                echo "Kullanım: ./backup.sh auto [update|migration|config] [açıklama]"
                ;;
        esac
        ;;
        
    "help" | "h" | "" | *)
        echo -e "${GREEN}📖 Kullanım Kılavuzu:${NC}"
        echo ""
        echo -e "${CYAN}Temel Komutlar:${NC}"
        echo "  ./backup.sh create [açıklama]    - Yeni checkpoint oluştur"
        echo "  ./backup.sh list                 - Timeline'ı listele"
        echo "  ./backup.sh rollback [id]        - Checkpoint'e geri dön"
        echo ""
        echo -e "${CYAN}Otomatik Backup:${NC}"
        echo "  ./backup.sh daily                - Günlük backup"
        echo "  ./backup.sh weekly               - Haftalık backup"
        echo "  ./backup.sh cleanup [gün]        - Eski backup'ları temizle"
        echo ""
        echo -e "${CYAN}Özel Durumlar:${NC}"
        echo "  ./backup.sh emergency            - Acil rollback menüsü"
        echo "  ./backup.sh status               - Sistem durumu"
        echo ""
        echo -e "${CYAN}Otomatik Checkpoint'ler:${NC}"
        echo "  ./backup.sh auto update          - Güncelleme öncesi"
        echo "  ./backup.sh auto migration       - Migration öncesi"
        echo "  ./backup.sh auto config          - Config değişikliği öncesi"
        echo ""
        echo -e "${CYAN}Kısa Komutlar:${NC}"
        echo "  c = create, l = list, r = rollback"
        echo "  d = daily, w = weekly, s = status, em = emergency"
        echo ""
        echo -e "${YELLOW}💡 İpucu: Kritik işlemler öncesi mutlaka checkpoint oluşturun!${NC}"
        ;;
esac

echo ""
echo -e "${CYAN}🔗 Web arayüzü: https://magazatakip.com.tr/admin/timeline${NC}"