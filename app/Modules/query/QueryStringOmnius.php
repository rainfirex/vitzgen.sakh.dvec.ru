<?php namespace App\Modules\query {

    use App\Models\BaseRegion;

    trait QueryStringOmnius
    {
        /**
         * Начисление Omnius (MS)
         * @param string $date
         * @param  BaseRegion $Base
         * @return string
         */
        public final static function calculateOmniusMicrosoft(string $date, BaseRegion $Base) {
            $replace_string = 'г Южно-Сахалинск,';
            return "SELECT 
                SS.C_Number			AS lic, 
                SS.N_Code			AS C_Code_Subscr,
                case  when scp.F_Towns = 9917 
                      then concat('693000,',CONCAT(
                        (CASE when SCP.F_Streets is null 
                          THEN concat(SCP.C_Address_Short,',',SCP.N_Building_Num)
                          WHEN scp.F_Towns = 9917 AND scp.F_Municipalities is not NULL 
                          THEN REPLACE(scp.C_Address_Short,'".$replace_string."','')
                          else SCP.C_Address_Short end),',',ss.N_Premise_Number))
                  ELSE
                      CONCAT(
                        (CASE when SCP.F_Streets is null 
                          THEN concat(SCP.C_Address_Short,',',SCP.N_Building_Num)
                          else SCP.C_Address_Short end),',',ss.N_Premise_Number)  end as C_Address,
                REPLACE(CONCAT(cp.C_Name1,' ',cp.C_Name2,' ',cp.C_Name3),',','|') as fio,
                cast(SUM(FSS.N_Amount0) as DECIMAL(10,2))	AS N_Debt_Amount,
                cast(SUM(FSS.N_Debit) as DECIMAL(10,2))	AS N_Charge_Amount, 
                cast(SUM(FSS.N_Credit) as DECIMAL(10,2))	AS oplata, 
                cast(SUM(FSS.N_Amount1) as DECIMAL(10,2))	AS saldo_end
                
                FROM PE.FF_Saldo_Simple(".$Base->omnius_division.",".$date.",".$date.",0) AS FSS  
                INNER JOIN dbo.SD_Subscr AS SS
                ON SS.F_Division = FSS.F_Division
                AND SS.LINK = FSS.F_Subscr
                INNER JOIN dbo.SD_Conn_Points AS SCP
                ON SCP.LINK = SS.F_Conn_Points
                INNER JOIN CD_Partners cp on SS.F_Partners = cp.LINK
                WHERE FSS.F_Sale_Categories = 39
                -- and ss.N_Code in   (110415033, 110906131,  111217035, 112008129,111909003, 110904864,111907179)
                
                GROUP BY cp.C_Name1,cp.C_Name2,cp.C_Name3, 
                    FSS.N_Period ,
                     SS.C_Number ,
                     SS.N_Code ,
                     SCP.C_Address_Short,
                      scp.F_Municipalities,
                      SCP.F_Towns,
                     SCP.F_Streets,
                     SCP.N_Building_Num,
                ss.N_Premise_Number
                -- HAVING ( FSS.N_Debit > 40000)
                order by 2;";
        }
    }
}
