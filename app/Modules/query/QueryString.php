<?php namespace App\Modules\query {

    use App\Models\BaseRegion;
    use App\Models\TypeReport;
    use App\Modules\db\MicrosoftDB;
    use App\Modules\db\OracleDB;

    /**
     * Выгрузка извещений по участкам
     * Class QueryString
     * @package App\Modules
     */
    class QueryString
    {
        use QueryStringAsuse;
        use QueryStringOmnius;

        private $query;
        private $file;
        private $date;

        /**
         * Тестовый
         */
        const F_TEST = 'test.';
        /**
         * По лицевым счетам
         */
        const F_PERSONAL_ACCOUNTS ='personal_accounts.';
        /**
         * Начисление по лс с сальдо все
         */
        const F_PERSONAL_ACCOUNTS_BALANCE = 'personal_accounts_balance.';
        /**
         * ИПУ и Показания ИПУ
         */
        const F_IPU_INDICATIONS = 'ipu_indications.';
        /**
         * Показания ОДПУ
         */
        const F_ODPU_INDICATIONS = 'odpu_indications.';
        /**
         * Платежи
         */
        const F_PAYMENTS = 'payments.';
        /**
         * Начисление Omnius
         */
        const F_CALCULATE_OMNIUS = 'начисление_omnius.';
        /**
         * Начисление Asuse
         */
        const F_CALCULATE_ASUSE = 'начисление_asuse.';

        /**
         * @return mixed
         */
        public function getDate()
        {
            return $this->date;
        }

        /**
         * @param mixed $date
         */
        public function setDate($date): void
        {
            $this->date = $date;
        }

        /**
         * @return mixed
         */
        public function getQuery()
        {
            return $this->query;
        }

        /**
         * @param mixed $query
         */
        public function setQuery($query): void
        {
            $this->query = $query;
        }

        /**
         * @return mixed
         */
        public function getFile()
        {
            return $this->file;
        }

        /**
         * @param mixed $file
         */
        public function setFile($file): void
        {
            $this->file = $file;
        }

        /**
         * инициализация
         * @param string $date
         * @param BaseRegion $Base
         * @param TypeReport $Report
         * @return QueryString|null
         */
        public function init(string $date, BaseRegion $Base, TypeReport $Report): ?QueryString {
            $asuseDate = OracleDB::dateFormatter($date);
            $omniusDate = MicrosoftDB::dateFormatter($date);

            switch ($Report->title) {
                case 'По лицевым счетам':
                    $this->file = self::F_PERSONAL_ACCOUNTS;
                    $this->query = self::queryPersonalAccounts($Base); //empty
                    $this->date = $asuseDate;
                    break;
                case 'Начисление по лс с сальдо все':
                    $this->file = self::F_PERSONAL_ACCOUNTS_BALANCE;
                    $this->query = self::query2($asuseDate, $Base, $Report); //empty || ERROR
                    $this->date = $asuseDate;
                    break;
                case 'ИПУ и Показания ИПУ':
                    $this->file = self::F_IPU_INDICATIONS;
                    $this->query = self::queryIPU($asuseDate); // ?OK
                    $this->date = $asuseDate;
                    break;
                case 'Показания ОДПУ':
                    $this->file = self::F_ODPU_INDICATIONS;
                    $this->query = self::queryODPU($asuseDate); // ?OK
                    $this->date = $asuseDate;
                    break;
                case 'Платежи':
                    $this->query = self::queryPayment($asuseDate, $Base); // ?OK
                    $this->file = self::F_PAYMENTS;
                    $this->date = $asuseDate;
                    break;
                case 'Начисление Omnius':
                    $this->query = self::calculateOmniusMicrosoft($omniusDate, $Base); // ?OK!
                    $this->file = self::F_CALCULATE_OMNIUS;
                    $this->date = $omniusDate;
                    break;
                case 'Начисление Asuse':
                    $this->query = self::calculateAsuseOracle($asuseDate, $Base); // ?OK!
                    $this->file = self::F_CALCULATE_ASUSE;
                    $this->date = $asuseDate;
                    break;
                default:
                    return null;
            }
            return $this;
        }
    }
}
