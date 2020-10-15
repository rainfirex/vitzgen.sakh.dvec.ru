<?php namespace App\Modules\query {

    use App\Models\BaseRegion;
    use App\Models\TypeReport;

    trait QueryStringAsuse
    {
        /**
         * Тестовый запрос к тестовой базе
         * @return string
         */
        public final static function test(): string {
            // $pao = ' \'ПАО "САХАЛИНЭНЕРГО"\'  ';
            // echo($pao);exit;
            // return 'SELECT 1,2,3, \'ПАО "ывмп"\' abc FROM rs_esys';

            return "SELECT * from rs_esys";
        }

        /**
         * По лицевым счетам
         * @param BaseRegion $Base
         * @return string
         */
        protected final static function queryPersonalAccounts(BaseRegion $Base) : string
        {
            return "SELECT distinct p.kodp S_Subscr_ID,
        6500000024 C_Org_INN,
        'ПАО \"САХАЛИНЭНЕРГО\"' C_Org_Name,
        kg.kf_nyear(sysdate) ||'-'||
        (case when LENGTH( kg.kf_nmonth(sysdate))=1 then 0|| kg.kf_nmonth(sysdate)
        else TO_CHAR(kg.kf_nmonth(sysdate)) end)||'-'||
        (case when LENGTH( kg.kf_nday(sysdate))=1 then 0|| kg.kf_nday(sysdate)
         else TO_CHAR(kg.kf_nday(sysdate)) end) D_Date_Export,
         -- Адрес дома
         mp.guid S_FIAS_ID,
        nvl  ( dom.kod_r, dom.kodd) S_House_ID,
        case when  dom.kod_r is not null then nk_adress.kf_address (3, dom.kod_r) else nk_adress.kf_address (3, dom.kodd) end as  C_House_Address,
        null  C_Municipality_Code,
        case when adr.name_s  like '%г' then SUBSTR(adr.name_s,1,LENGTH(adr.name_s) - INSTR(REVERSE(adr.name_s),' ',1))
        else  SUBSTR(adr1.name_s,1,LENGTH(adr1.name_s) - INSTR(REVERSE(adr1.name_s),' ',3)) end  C_Municipality_Name ,
        null C_Nas_Punkt_Code,
        case when adr.name_s  not  like '%г' then SUBSTR(adr.name_s,1,LENGTH(adr.name_s) - INSTR(REVERSE(adr.name_s),' ',1))
        else  null  end  C_Nas_Punkt_Name,
        case when ( select distinct
        tadr.name
         from kk_tnp tadr
        where 1=1
        and tadr.kod_tnp= adr.ur)like 'Город%'  then 405 else  430 end as  C_Nas_Punkt_Type,
        null  C_Street_Code,
        SUBSTR(strits.NAME,1,LENGTH(strits.NAME) - INSTR(REVERSE(strits.NAME),' ',1)) C_Street_Name,
        case when  ( select distinct
        tadr.kind
         from k_strits_index tadr
        where 1=1
        and tadr.kod= strits.kod) like 'пер%'   then 514 else
        case when  strits.NAME like '%туп'   then 528 else
         529  end end as   N_Street_Type,
        nvl(dom.nd|| (case when dom.nd_str is not  null then (' '||dom.nd_str) else null end )
        ||
        (case when dom.nd2 is not  null then dom.nd2 else null end )
        || (case when dom.nk is not  null then (' '||dom.nk) else null end )
        || (case when dom.remark is not  null then dom.remark else null end )
        || (case when dom.nstr is not  null then 'стр.'||dom.nstr else null end )
        , 'Нет дома')
        C_Building_Num,
        null C_Building_Corp_Type,
        -- dom.*,
        /*dom.nd1||
        (case when dom.nd2 is not  null then ('/'||dom.nd2) else null end ) ||
        (case when dom.nk is not  null then ('/'||dom.nk) else null end ) ||
        (case when dom.nd_str is not  null then ('/'||dom.nd_str) else null end ) ||
        (case when dom.remark is not  null then ('/'||dom.remark) else null end )
          AS C_Building_Corp,*/
          dom.nd1--||
        --(case when dom.nd2 is not  null then dom.nd2 else null end ) ||
        --(case when dom.nk is not  null then dom.nk else null end ) ||
        --(case when dom.nd_str is not  null then dom.nd_str else null end ) ||
        --(case when dom.remark is not  null then dom.remark else null end )
          AS C_Building_Corp,

            -- Характеристики помещения
            dom.flat   C_Premise_Number,
          1  C_Premise_Type,
          null    C_Property_Type,
          null D_Date_Property_Change,

        (select max(  ob.square)
         FROM  kr_numobj n,
         kr_object ob
                 WHERE   1=1
         AND d.kod_dog = n.kod_dog(+)
         AND ob.kod_obj(+) = n.kod_obj
        and ( ob.dep in ($Base->asuse_code_dep)  or  nk_adress.kf_address (5,  ob.kodd)  like '%Костромское%'
          or  nk_adress.kf_address (5, ob.kodd)  like '%Пионеры%'  )
         AND n.dat_fin IS NULL
         and ob.square is not null
         )  N_Square_Total,

        ( select   MAX(t.S_GIL)
         FROM kr_numobj n,
         kr_object ob,tr_har_obj t
                 WHERE     1=1
         AND d.kod_dog = n.kod_dog(+)
         AND ob.kod_obj(+) = n.kod_obj
         AND n.dat_fin IS NULL
         and  t.s_gil  is not null
         and ( ob.dep in ($Base->asuse_code_dep)  or  nk_adress.kf_address (5,  ob.kodd)  like '%Костромское%'
          or  nk_adress.kf_address (5, ob.kodd)  like '%Пионеры%'  )
         AND ob.kod_obj = t.kod_obj(+)

         ) N_Square_Live ,
        (select  case when max(  ob.d_m) is null then null else
        kg.kf_nyear(max(  ob.d_m)) ||'-'||
        (case when LENGTH( kg.kf_nmonth(max(  ob.d_m)))=1 then 0|| kg.kf_nmonth(max(  ob.d_m))
        else TO_CHAR(kg.kf_nmonth(max(  ob.d_m))) end)||'-'||
        (case when LENGTH( kg.kf_nday(max(  ob.d_m)))=1 then 0|| kg.kf_nday(max(  ob.d_m))
         else TO_CHAR(kg.kf_nday(max(  ob.d_m))) end) end -- max(  ob.d_m)
         FROM  kr_numobj n,
         kr_object ob
                 WHERE   1=1
         AND d.kod_dog = n.kod_dog(+)
         AND ob.kod_obj(+) = n.kod_obj
        and ( ob.dep in ($Base->asuse_code_dep)  or  nk_adress.kf_address (5,  ob.kodd)  like '%Костромское%'
          or  nk_adress.kf_address (5, ob.kodd)  like '%Пионеры%'  )
         AND n.dat_fin IS NULL
         and ob.square is not null
         )   D_Date_Squares_Change,

         d.kol_kom N_Rooms_Count,
        nvl(( select   MAX(decode ( pl.kod_plita, 1,1,0,0,2,1))
         FROM kr_numobj n,lk_plita pl,
         kr_object ob,tr_har_obj t
                 WHERE     1=1
         AND d.kod_dog = n.kod_dog(+)
         AND ob.kod_obj(+) = n.kod_obj
         AND n.dat_fin IS NULL
         AND pl.kod_plita(+) = t.kod_plita
         and t.kod_plita is not null
         and ( ob.dep in ($Base->asuse_code_dep)  or  nk_adress.kf_address (5,  ob.kodd)  like '%Костромское%'
          or  nk_adress.kf_address (5, ob.kodd)  like '%Пионеры%'  )
         AND ob.kod_obj = t.kod_obj(+)

         ),1) B_Electro_Cooker,

           -- Характеристики Л/С
         p.nump  C_Code_Subscr,
        null C_Code_Subscr_Add,
         kg.kf_nyear(d.dat_dog) ||'-'||
        (case when LENGTH( kg.kf_nmonth(d.dat_dog))=1 then 0|| kg.kf_nmonth(d.dat_dog)
        else TO_CHAR(kg.kf_nmonth(d.dat_dog)) end)||'-'||
        (case when LENGTH( kg.kf_nday(d.dat_dog))=1 then 0|| kg.kf_nday(d.dat_dog)
         else TO_CHAR(kg.kf_nday(d.dat_dog)) end)  D_Date_Begin,
        case when d.dat_fin is null then null else kg.kf_nyear(d.dat_fin) ||'-'||
        (case when LENGTH( kg.kf_nmonth(d.dat_fin))=1 then 0|| kg.kf_nmonth(d.dat_fin)else TO_CHAR(kg.kf_nmonth(d.dat_fin)) end)
        ||'-'||
        (case when LENGTH( kg.kf_nday(d.dat_fin))=1 then 0|| kg.kf_nday(d.dat_fin)else TO_CHAR( kg.kf_nday(d.dat_fin)) end) end as   D_Date_End,
        --p.NAME AS C_Subscr_Family,
        nvl((   SELECT
        emp.fio_f
        From kr_employee emp
        WHERE  emp.kodp= d.kodp
        and emp.kod_numobj is not null
                AND emp.kod_dolzhfun = 14
               ), 'нет зарегистрированных')  AS C_Subscr_Family,
                (   SELECT
        emp.fio_i
        From kr_employee emp
        WHERE  emp.kodp= d.kodp
        and emp.kod_numobj is not null
                AND emp.kod_dolzhfun = 14
               ) AS C_Subscr_Name,
                  (   SELECT
        emp.fio_o
        From kr_employee emp
        WHERE  emp.kodp= d.kodp
        and emp.kod_numobj is not null
                AND emp.kod_dolzhfun = 14
               ) AS C_Subscr_Otchestvo,

        (SELECT
         substr(TO_CHAR( wm_concat(e.e_mail )),1,100)  AS NAME
         FROM kr_employee e
                 WHERE 1=1
                 and     e.kodp = d.kodp
         AND e.kod_numobj IS  NULL
         and   e.e_mail is not null)C_Subscr_Email,
        (SELECT
          substr(TO_CHAR( wm_concat(a.nom)),1,100)  AS NAME
            FROM kr_phone a
           where 1=1
           and  a.kodp = p.kodp)C_Subscr_Phones,
        /*(select  t.sqoi
         from  kr_object ob, tr_har_house t
        where 1=1
        and t.kod_obj= ob.kod_obj
        and nvl  ( dom.kod_r, dom.kodd)= ob.kodd
        and  t.sqoi is not null ) N_Square_Public,
        null  B_Cook_Type,*/
         t_byt_priem.get_kol_chel_by_kodp (d.kodp, 0, SYSDATE) AS N_Registers,
         t_byt_priem.get_kol_chel_by_kodp (d.kodp, 1, SYSDATE) AS N_Live_People,
        null  N_Absent_People,
        case when (SELECT   MAX (prozh.datevv) \"проживает с\"
            FROM  tr_prop prozh
           WHERE 1=1
             AND prozh.pr_prop = 0
              AND prozh.kodp(+) = p.kodp) is null then null else
               kg.kf_nyear((SELECT   MAX (prozh.datevv) \"проживает с\"
            FROM  tr_prop prozh
           WHERE 1=1
             AND prozh.pr_prop = 0
              AND prozh.kodp(+) = p.kodp)) ||'-'||
        (case when LENGTH( kg.kf_nmonth((SELECT   MAX (prozh.datevv) \"проживает с\"
            FROM  tr_prop prozh
           WHERE 1=1
             AND prozh.pr_prop = 0
              AND prozh.kodp(+) = p.kodp)))=1 then 0|| kg.kf_nmonth((SELECT   MAX (prozh.datevv) \"проживает с\"
            FROM  tr_prop prozh
           WHERE 1=1
             AND prozh.pr_prop = 0
              AND prozh.kodp(+) = p.kodp))
        else TO_CHAR(kg.kf_nmonth((SELECT   MAX (prozh.datevv) \"проживает с\"
            FROM  tr_prop prozh
           WHERE 1=1
             AND prozh.pr_prop = 0
              AND prozh.kodp(+) = p.kodp))) end)||'-'||
        (case when LENGTH( kg.kf_nday((SELECT   MAX (prozh.datevv) \"проживает с\"
            FROM  tr_prop prozh
           WHERE 1=1
             AND prozh.pr_prop = 0
              AND prozh.kodp(+) = p.kodp)))=1 then 0|| kg.kf_nday((SELECT   MAX (prozh.datevv) \"проживает с\"
            FROM  tr_prop prozh
           WHERE 1=1
             AND prozh.pr_prop = 0
              AND prozh.kodp(+) = p.kodp))
         else TO_CHAR(kg.kf_nday((SELECT   MAX (prozh.datevv) \"проживает с\"
            FROM  tr_prop prozh
           WHERE 1=1
             AND prozh.pr_prop = 0
              AND prozh.kodp(+) = p.kodp))) end)  end
              D_Date_People_Change

          FROM kr_dogovor d,       kr_org o,kr_Adr_map_f mp,
             --  kr_employee e,       kr_org o1,
               k_house dom,       k_strits strits,
               adr_m adr,       adr_m adr1,  adr_m adr2,
               kr_payer p
         WHERE     d.kodp = p.kodp
         AND d.tep_el + 0 = 4
         AND d.tep_el_byt = 1
         AND p.kod_d = dom.kodd
         AND dom.kod_s = strits.kod
         AND strits.kod_m = adr.kod_m
        AND adr.k_npw = adr1.kod_m(+)
          AND adr1.k_npw = adr2.kod_m(+)
         AND o.KODP(+) = d.dep
        -- AND d.podr = o1.kodp(+)
        -- AND d.kod_emp = e.kod_emp(+)

         and ( d.dep in ($Base->asuse_code_dep)  or  nk_adress.kf_address (5,  p.kod_d)  like '%Костромское%'
          or  nk_adress.kf_address (5, p.kod_d)  like '%Пионеры%'  )
        --and p.nump='170530046'
        and nvl  ( dom.kod_r, dom.kodd) = mp.kod_asuse(+)
        --and p.kodp in (10176255,10176258)
        ORDER BY 7";
        }

        /**
         * Начисление по лс с сальдо все
         * @param string $date
         * @param BaseRegion $Base
         * @param TypeReport $Report
         * @return string
         */
        protected final static function query2(string $date, BaseRegion $Base, TypeReport $Report): string
        {
            return "
            select * from (select
p.nump N_Code ,
p.kodp S_Subscr_ID,
 case when   kint.name ='Суточная' and   acc.vid_t in (25,-26) then 231  else
    case when   kint.name ='2-х зонн. день' and   acc.vid_t in (25,-26) then 232 else
   case when   kint.name ='2-х зонн. ночь' and   acc.vid_t in (25,-26) then 233  else
   case when   kint.name ='Суточная' and   acc.vid_t in (-37) then 2314  else
 case when   kint.name ='Суточная' and  acc.vid_t in (35,-36) then 1231  else
    case when   kint.name ='2-х зонн. день' and   acc.vid_t in (35,-36) then 1232 else
   case when   kint.name ='2-х зонн. ночь' and   acc.vid_t in (35,-36) then 1233  else
   231 end end end  end end end end as N_Sale_Item,

  replace ( acc.ym, '.','')  N_Period,
case when acc.vid_t in (35, -36) then 2 else 1 end as N_Calc_Type,
DECODE(
            NVL(
               t_byt_priem.get_real_calctype(pr.calctypen,
                                              pr.calctype_rasch),
               pr.calctypen
            ),
            0,
           2,
            null,
            2,
            6,
            2,
            12,
            2,
             8,
            2,
            7,

            1,
            9,
            1,
            1,
            3,
            11,
            4
         ) N_Calc_Method,
sum(case when acc.rym is null and  acc.vid_t not in (141)  then acc.cust  else 0 end)  N_Quantity,
  t.name C_Tariff_Name,
nvl(  acc.price, 0) N_Tariff,
  0 N_Debit,
sum(case when acc.rym is null and  acc.vid_t not in (141) then acc.nachisl  else 0 end)  N_Calc,
 case when acc.vid_t = -26 then 1.5  else null end as N_Coeff,

sum( case when acc.vid_t = -26 then acc.nachisl  else null end) as N_Calc_Koeff_more,


sum(case when acc.rym is not null or acc.vid_t  in (141)then acc.nachisl  else null end) as N_Recalc_Sum,
substr(TO_CHAR(
 wm_concat(distinct
   r.NAME
)
), 1, 100) C_Recalc_reason,
--sum(acc.nachisl)  N_Calc_Sum,
sum( CASE when acc.vid_t in (-37) then 0 ELSE acc.nachisl END)  N_Calc_Sum,

nvl(rd.rd, 0) N_Quantity_Sum,
replace((select
 substr(TO_CHAR(wm_concat(distinct  n2.rasx)), 1, 100)
   FROM
  ts_mop_const_rasx n2,
  hr_point_rasx_obj  n4, kr_object ob
  where 1 = 1
and n4.kod_mop_const_rasx_name = n2.kod_mop_const_rasx_date(+)
and n4.dat_po is null
and  n4.kod_obj(+) = ob.kod_obj
and ob.kodd = h.kod_r),'.',',')  N_Norm_Soi,

max(T_V_COUNT.Normativ(d.kod_dog, pr.kod_point, kg.ym_last_day(acc.ym)))N_Norm,
nvl(h.kod_r, h.kodd) S_House_ID,
null

N_Rec_Debt


from tnr_account acc, kr_dogovor d,    ks_tarif t, k_house h,
    tnr_priem pr, ss_vid_recalc r,   kr_payer p, kk_interval kint,
    (select  nvl(sum(nd.out), 0) as rd,  ob.kodd
 from tnr_priem_house nd, hr_point po,kr_object ob
 where nd.ym =$date
 and po.kod_point = nd.kod_point
  and po.nagruz_type not  in(4)
  and ob.kod_obj = po.kod_obj
  group by ob.kodd  ) rd
where 1 = 1
and acc.ym =$date
 and acc.vid_t not in (48, 47)

and acc.kod_dog = d.kod_dog
and d.kodp = p.kodp
and pr.kod_priem(+) = acc.kod_priem
and pr.num_priem(+) = acc.num_priem
AND p.kod_d = h.kodd
  and acc.tarif = t.tarif
   and t.kodinterval = kint.kodinterval(+)
   AND acc.vid_recalc = r.vid_recalc(+)
   and nvl(h.kod_r , h.kodd)= rd.kodd(+)
    and(d.dep in ($Base->asuse_code_dep)  or  nk_adress.kf_address(5, p.kod_d)  like '%Костромское%'
  or  nk_adress.kf_address(5, p.kod_d)  like '%Пионеры%')
group by  p.nump  ,h.kod_r,nvl(rd.rd, 0),
nvl(h.kod_r, h.kodd) ,d.kod_dog,
p.kodp ,
 replace(acc.ym, '.', '') ,
 case when kint.name = 'Суточная' and acc.vid_t in (25, -26) then 231  else
    case when kint.name = '2-х зонн. день' and acc.vid_t in (25, -26) then 232 else
   case when kint.name = '2-х зонн. ночь' and acc.vid_t in (25, -26) then 233  else
   case when   kint.name ='Суточная' and   acc.vid_t in (-37) then 2314  else
 case when kint.name = 'Суточная' and acc.vid_t in (35, -36) then 1231  else
    case when kint.name = '2-х зонн. день' and acc.vid_t in (35, -36) then 1232 else
   case when kint.name = '2-х зонн. ночь' and acc.vid_t in (35, -36) then 1233  else
    231 end end end  end end end end ,
   DECODE (
            NVL(
              t_byt_priem.get_real_calctype(pr.calctypen,
                                             pr.calctype_rasch),
              pr.calctypen
           ),
           0,
          2,
           null,
           2,
           6,
           2,
           12,
           2,
            8,
           2,
           7,

           1,
           9,
           1,
           1,
           3,
           11,
           4
         ) ,
         case when acc.vid_t in (35, -36) then 2 else 1 end,
  t.name ,
  acc.price,
 case when acc.vid_t = -26 then 1.5  else null end

   union all
   SELECT
 p.nump N_Code,
p.kodp S_Subscr_ID,
231  N_Sale_Item,
 replace( $date, '.', '')  N_Period,


 1  N_Calc_Type,
null  N_Calc_Method,
null  N_Quantity,
null C_Tariff_Name,
0 N_Tariff,

        sum(NVL((SELECT SUM(acc.nachisl)
                   FROM tnr_account acc, SK_VID_REAL r, sk_nachisl n
                  WHERE acc.kod_dog = d.kod_dog
                  and(acc.dep in ($Base->asuse_code_dep)  or  nk_adress.kf_address(5, p.kod_d)  like '%Костромское%'
  or  nk_adress.kf_address(5, p.kod_d)  like '%Пионеры%')
                and n.vid_real = r.vid_real
 and n.vid_t = acc.vid_t
 and r.vid_real in (2, 4)

                   AND acc.ym < $date), 0)
         - NVL((SELECT SUM(o.opl)
                   FROM tsr_opl o
                  WHERE o.kod_dog = d.kod_dog
                 and  o.vid_real in (2, 4)
                  and(o.dep in ($Base->asuse_code_dep)  or  nk_adress.kf_address(5, p.kod_d)  like '%Костромское%'
  or  nk_adress.kf_address(5, p.kod_d)  like '%Пионеры%')
                  AND o.ym < $date
                  ), 0)) N_Debit,
null  N_Calc,
null as N_Coeff,
null as N_Calc_Koeff_more,
null as N_Recalc_Sum,
null C_Recalc_reason,
null  N_Calc_Sum,
null N_Quantity_Sum,
null  N_Norm_Soi,
null N_Norm,
nvl(h.kod_r, h.kodd) S_House_ID,
0 N_Rec_Debt--,


FROM kr_payer p, k_house h,   kr_dogovor d
   WHERE 1 = 1
     AND d.tep_el + 0 = 4
     AND d.tep_el_byt = 1
     AND d.kodp = p.kodp
   and(d.dep in ($Base->asuse_code_dep)  or  nk_adress.kf_address(5, p.kod_d)  like '%Костромское%'
  or  nk_adress.kf_address(5, p.kod_d)  like '%Пионеры%')
       AND p.kod_d = h.kodd

   group by  p.nump  ,
p.kodp,replace( $date, '.', ''),
  nvl(h.kod_r, h.kodd)
  ) t
  where 1 = 1
  and(t.N_Debit <> 0  or   t.n_calc_sum <> 0)

union all
select* from(

select
 p.nump N_Code,
p.kodp S_Subscr_ID,
110 N_Sale_Item,
  replace(acc.ym, '.', '')  N_Period,

3  N_Calc_Type,
null  N_Calc_Method,
null  N_Quantity,
null  C_Tariff_Name,
null  N_Tariff,
  0 N_Debit,
sum(case when acc.rym is null then acc.nachisl  else 0 end)  N_Calc,
null as N_Coeff,
null as N_Calc_Koeff_more,
sum(case when acc.rym is not null then acc.nachisl  else null end) as N_Recalc_Sum,
substr(TO_CHAR(
 wm_concat(distinct
   r.NAME
)
), 1, 100) C_Recalc_reason,
--sum(acc.nachisl)  N_Calc_Sum,
sum( CASE when acc.vid_t in (-37) then 0 ELSE acc.nachisl END)  N_Calc_Sum,
null  N_Quantity_Sum,
null   N_Norm_Soi,
null N_Norm,
nvl(h.kod_r, h.kodd) S_House_ID,
0 N_Rec_Debt--,


from tnr_account acc, kr_dogovor d,   k_house h,
   ss_vid_recalc r,   kr_payer p
where 1 = 1
and acc.ym =$date
 and acc.vid_t  in (48, 47)
and acc.kod_dog = d.kod_dog
and d.kodp = p.kodp
AND p.kod_d = h.kodd
AND acc.vid_recalc = r.vid_recalc(+)
 and(d.dep in ($Base->asuse_code_dep)  or  nk_adress.kf_address(5, p.kod_d)  like '%Костромское%'
  or  nk_adress.kf_address(5, p.kod_d)  like '%Пионеры%')

group by  p.nump  ,h.kod_r,
nvl(h.kod_r, h.kodd) ,d.kod_dog,
p.kodp ,
 replace(acc.ym, '.', '')



 union all
 SELECT
 p.nump N_Code,
p.kodp S_Subscr_ID,
110 N_Sale_Item,
  replace( $date, '.', '')  N_Period,

 1  N_Calc_Type,
null  N_Calc_Method,
null  N_Quantity,
null C_Tariff_Name,
0 N_Tariff,

        sum(NVL((SELECT SUM(acc.nachisl)
                   FROM tnr_account acc, SK_VID_REAL r, sk_nachisl n
                  WHERE acc.kod_dog = d.kod_dog
                  and(acc.dep in ($Base->asuse_code_dep)  or  nk_adress.kf_address(5, p.kod_d)  like '%Костромское%'
  or  nk_adress.kf_address(5, p.kod_d)  like '%Пионеры%')
                and n.vid_real = r.vid_real
 and n.vid_t = acc.vid_t
 and r.vid_real in (7)

                   AND acc.ym < $date), 0)
         - NVL((SELECT SUM(o.opl)
                   FROM tsr_opl o
                  WHERE o.kod_dog = d.kod_dog
                 and  o.vid_real in (7)
                  and(o.dep in ($Base->asuse_code_dep)  or  nk_adress.kf_address(5, p.kod_d)  like '%Костромское%'
  or  nk_adress.kf_address(5, p.kod_d)  like '%Пионеры%')
                  AND o.ym < $date
                  ), 0)) N_Debit,
null  N_Calc,
null as N_Coeff,
null as N_Calc_Koeff_more,
null as N_Recalc_Sum,
null C_Recalc_reason,
null  N_Calc_Sum,
null N_Quantity_Sum,
null  N_Norm_Soi,
null N_Norm,
nvl(h.kod_r, h.kodd) S_House_ID,
0 N_Rec_Debt--,

FROM kr_payer p, k_house h,  kr_dogovor d


   WHERE 1 = 1
     AND d.tep_el + 0 = 4
     AND d.tep_el_byt = 1
     AND d.kodp = p.kodp
   and(d.dep in ($Base->asuse_code_dep)  or  nk_adress.kf_address(5, p.kod_d)  like '%Костромское%'
  or  nk_adress.kf_address(5, p.kod_d)  like '%Пионеры%')
       AND p.kod_d = h.kodd

   group by  p.nump  , replace( $date, '.', '') ,
p.kodp,
  nvl(h.kod_r, h.kodd)) t
where 1 = 1
 and(t.N_Debit <> 0  or   t.n_calc_sum <> 0)
-- )

order by 1

            ";
        }

        /**
         * ИПУ и Показания ИПУ
         * @param string $date
         * @return string
         */
        protected final static function queryIPU(string $date): string
        {
            return "
                    SELECT distinct
                p.nump AS N_Code,
               p.kodp S_Subscr_ID ,
            case when   kint.name ='Суточная'  then 231  else
            case when   kint.name ='2-х зонн. день'  then 232 else
           case when   kint.name ='2-х зонн. ночь'  then 233
            else 231 end end end  as N_Sale_Item,
               replace ( pr.ym, '.','')N_Period,
                pu.nom_pu C_Serial_Number ,
               hg_pasp_pu_r.name_pu (puu.kod_tippu, 2) as C_Device_Type,
                 pu.kod_point_pu S_Device_ID ,
        case when pu.dat_s is not null then TO_CHAR(pu.dat_s,'YYYY-MM-DD') else  null end as D_Date_Begin,
        case when pu.dat_po >kg.ym_last_day ( pr.ym) or pu.dat_po is null  then null else TO_CHAR(pu.dat_po,'YYYY-MM-DD') end as  D_Date_End,
        case when   kint.name ='Суточная' and   acc.vid_t in (25,-26) then 0  else
        case when   kint.name ='2-х зонн. день' and   acc.vid_t in (25,-26) then 4 else
        case when   kint.name ='2-х зонн. ночь' and   acc.vid_t in (25,-26) then 3
        else 0 end end end    as N_Time_Zone,

        to_char(nvl(pr5.dddt,pu.dat_s),'YYYY-MM-DD') as D_Date_Pre,
        case when pr5.readlast is null then pr.readprev else pr5.readlast end n_value_prev,

        case when pr.calctype_rasch=0 then case when pr.dat_byt is not null then TO_CHAR(pr.dat_byt,'YYYY-MM-DD') else  null end else  null end as D_Date_Cur,
        case when pr.calctype_rasch=0 then pr.readlast else null end as N_Value_Cur,


        ( select sum (acc1.cust) from   tnr_account acc1
           where 1=1
            and  acc1.kod_dog=acc.kod_dog
              and acc1.kod_priem=acc.kod_priem
            and acc1.num_priem=acc.num_priem
            and acc1.ym=acc.ym
            and acc1.tarif=acc.tarif
            )  N_Quantity ,

         case when    nvl(a.kvartal_op,1)>=4
             then
             ( case when kg.ym_last_day(nvl(a.god_op,1960)|| '.' || 3*4) is not null then
        kg.kf_nyear( kg.ym_last_day(nvl(a.god_op,1960)|| '.' || 3*4)) ||'-'||
        (case when LENGTH( kg.kf_nmonth(kg.ym_last_day(nvl(a.god_op,1960)|| '.' || 3*4)))=1
        then 0|| kg.kf_nmonth(kg.ym_last_day(nvl(a.god_op,1960)|| '.' || 3*4))
        else TO_CHAR(kg.kf_nmonth(kg.ym_last_day(nvl(a.god_op,1960)|| '.' || 3*4))) end)||'-'||
        (case when LENGTH( kg.kf_nday(kg.ym_last_day(nvl(a.god_op,1960)|| '.' || 3*4)))=1
        then 0|| kg.kf_nday(kg.ym_last_day(nvl(a.god_op,1960)|| '.' || 3*4))
         else TO_CHAR(kg.kf_nday(kg.ym_last_day(nvl(a.god_op,1960)|| '.' || 3*4))) end)end )
         else kg.kf_nyear( kg.ym_last_day (nvl(a.god_op,1960)|| '.' || 0||3*nvl(a.kvartal_op,1))) ||'-'||
        (case when LENGTH( kg.kf_nmonth(kg.ym_last_day (nvl(a.god_op,1960)|| '.' || 0||3*nvl(a.kvartal_op,1))))=1 then
         0|| kg.kf_nmonth(kg.ym_last_day (nvl(a.god_op,1960)|| '.' || 0||3*nvl(a.kvartal_op,1)))
        else TO_CHAR(kg.kf_nmonth(kg.ym_last_day (nvl(a.god_op,1960)|| '.' || 0||3*nvl(a.kvartal_op,1)))) end)||'-'||
        (case when LENGTH( kg.kf_nday(kg.ym_last_day (nvl(a.god_op,1960)|| '.' || 0||3*nvl(a.kvartal_op,1))))=1
        then 0|| kg.kf_nday(kg.ym_last_day (nvl(a.god_op,1960)|| '.' || 0||3*nvl(a.kvartal_op,1)))
         else TO_CHAR(kg.kf_nday(kg.ym_last_day (nvl(a.god_op,1960)|| '.' || 0||3*nvl(a.kvartal_op,1)))) end)
                 end      as D_Date_Verification

        , case when nvl(ini.rkoeff,1) <= 1 then 1 else ini.rkoeff end  as k

        FROM kr_dogovor d,

                kr_org o,
               kr_payer p,
               kr_numobj n,
               kr_object o,
               hr_point po,
               hr_point_pu pu,
               hr_pu_u puu,
               TV_POINT_PU a,
               hr_point_en en,
               hr_point_ini ini,
               hr_point_tar tar,
               ks_tarif t,
               tnr_priem pr,
               tnr_account acc,
              kk_interval kint,

        ( Select pr3.kod_point,
                pr3.kod_point_ini,
                pr3.dat_byt,
                pr3.calctype_rasch,
                pr3.rnk,
                pr4.dat_byt dddt,
                pr4.calctype_rasch,
                pr4.rnk1,
                pr4.readlast
                from

        (select sss.*,rank() over(partition by kod_point,kod_point_ini order by dat_byt desc) rnk from
                (select kod_point,kod_point_ini,readlast,dat_byt,calctype_rasch from tnr_priem where ym=$date order by 1,4) sss where calctype_rasch<>11) pr3
        left outer join (select sss.*,rank() over(partition by kod_point ,kod_point_ini order by dat_byt desc) rnk1 from
                            (select kod_point,kod_point_ini,readlast,dat_byt,calctype_rasch from tnr_priem where ym<=$date order by 1,4) sss where calctype_rasch=0) pr4

        on
            pr3.kod_point=pr4.kod_point
            and pr3.kod_point_ini=pr4.kod_point_ini
            and pr3.rnk=1 and pr4.rnk1=case when pr3.calctype_rasch=0 then 2
                                        else 1
                                        end
        )pr5


         WHERE p.kodp = d.kodp
         and pr.kod_point_ini=ini.kod_point_ini(+)
         and pr.kod_point=pu.kod_point(+)
        and pr5.kod_point=pr.kod_point
        and pr5.kod_point_ini=pr.kod_point_ini

        --and pr2.readlast=pr.readprev
         and t.tarif=tar.tarif

         AND d.tep_el + 0 = 4
           AND d.tep_el_byt = 1
           AND d.kod_dog = n.kod_dog
           AND n.kod_obj = o.kod_obj
           AND po.kod_obj = o.kod_obj
           AND po.kod_point = pu.kod_point
           AND pu.kod_point_pu = en.kod_point_pu
           AND en.kod_point_en(+) = ini.kod_point_en
            AND tar.kod_numobj = n.kod_numobj
           AND ini.kod_point_tar = tar.kod_point_tar
           and t.kodinterval=kint.kodinterval(+)
           and pu.kod_pu_u=puu.kod_pu_u(+)
            and a.kod_point_pu= pu.kod_point_pu
           and a.nom_pu= pu.nom_pu
           and   pr.kod_priem IS NOT NULL
          and d.kod_dog=acc.kod_dog
           and acc.kod_priem=pr.kod_priem
           and acc.num_priem= pr.num_priem
           and acc.ym=pr.ym(+)
           and acc.tarif=t.tarif
           and pr.ym =$date
           AND d.podr = o.KODP
           and pr.calctype in (0,12,6)
        --  and p.nump='170105004'
         and d.pr_active in (0,2)
        and p.prizn_activ in (0,2)
        --and nvl(mp.kind,777) not in (1,2)
          order by 1,5,6
  ";
        }

        /**
         * Показания ОДПУ
         * @param string $date
         * @return string
         */
        protected final static function queryODPU(string $date): string {
            return " select  distinct
            ob.kodd S_House_ID,
            case when   i.name ='Суточная'  then 1231  else
                case when   i.name ='2-х зонн. день' then 1232 else
                    case when   i.name ='2-х зонн. ночь'  then 1233
                        else 1233
                    end
                end
             end as N_Sale_Item,
            replace ( pr.ym, '.','')N_Period,
            vdr.nom_pu  C_Serial_Number,
            hg_pasp_pu_r.name_pu(pu.kod_tippu, 2) C_Device_Type,
            vdr.kod_point_pu S_Device_ID,
            case when pu.dat_s is not null then TO_CHAR(pu.dat_s,'YYYY-MM-DD') else  null end as D_Date_Begin,
            case when pu.dat_po >kg.ym_last_day ( pr.ym) or pu.dat_po is null  then null else TO_CHAR(pu.dat_po,'YYYY-MM-DD') end as  D_Date_End,
            case when   i.name ='Суточная'  then 0  else
                case when   i.name ='2-х зонн. день'  then 4 else
               case when   i.name ='2-х зонн. ночь'  then 3
                else 0 end end end N_Time_Zone,
            case when  T_BYTSH_V.get_prev_value_date (vdr.kod_priem) is not null then
            kg.kf_nyear( T_BYTSH_V.get_prev_value_date (vdr.kod_priem)) ||'-'||
            (case when LENGTH( kg.kf_nmonth( T_BYTSH_V.get_prev_value_date (vdr.kod_priem)))=1 then 0||
             kg.kf_nmonth( T_BYTSH_V.get_prev_value_date (vdr.kod_priem))
            else TO_CHAR(kg.kf_nmonth( T_BYTSH_V.get_prev_value_date (vdr.kod_priem))) end)||'-'||
            (case when LENGTH( kg.kf_nday( T_BYTSH_V.get_prev_value_date (vdr.kod_priem)))=1
             then 0|| kg.kf_nday( T_BYTSH_V.get_prev_value_date (vdr.kod_priem))
             else TO_CHAR(kg.kf_nday(T_BYTSH_V.get_prev_value_date (vdr.kod_priem))) end) else null end as  D_Date_Pre,
            vdr.readprev N_Value_Pre,
              case when vdr.dat_byt is not null then
            kg.kf_nyear( vdr.dat_byt) ||'-'||(case when LENGTH( kg.kf_nmonth(vdr.dat_byt))=1 then 0|| kg.kf_nmonth(vdr.dat_byt)
            else TO_CHAR(kg.kf_nmonth(vdr.dat_byt)) end)||'-'||
            (case when LENGTH( kg.kf_nday(vdr.dat_byt))=1 then 0|| kg.kf_nday(vdr.dat_byt)
             else TO_CHAR(kg.kf_nday(vdr.dat_byt)) end) else null end as D_Date_Cur,

            vdr.readlast N_Value_Cur,
            vdr.outcounter N_Quantity--,

             from  tnr_priem pr,
              table (t_bytsh_v.get_odpu_priem (pr.kod_point, $date)) vdr,

              hs_tippu tpu, hr_pu_u puu,hr_point_pu pu,
             hr_point_ini ini,tnr_priem_house pp,
                   hr_point_tar ptar,
                     ks_tarif tar,
                    kk_interval i,
                   hr_point_en en,
                   hr_point pt,
                   kr_object ob,
            k_house dom

             where 1=1
             and pr.ym=$date
             and vdr.kod_priem is not null
              AND pr.calctype IN (0, 11)
               AND tpu.kod_tippu = pu.kod_tippu
                AND puu.kod_pu_u = pu.kod_pu_u
                and pu.kod_point_pu=vdr. kod_point_pu
                    AND vdr.kod_point_ini = ini.kod_point_ini
                     AND ptar.kod_point_tar = ini.kod_point_tar
                     AND tar.tarif = ptar.tarif
                     AND i.kodinterval = tar.kodinterval
                    AND ini.kod_point_en = en.kod_point_en
                     AND pu.kod_point_pu = en.kod_point_pu
                     AND vdr.kod_point = pt.kod_point
                    AND ob.kod_obj = pt.kod_obj
                    AND ob.kodd = dom.kodd(+)
                   and pp.kod_priem=vdr.kod_priem
            --@dep
            ";
        }

        /**
         * Оплата
         * @param string $date
         * @param BaseRegion $Base
         * @return string
         */
        protected final static function queryPayment(string $date, BaseRegion $Base): string {
            return " select
            N_Code,
                   S_Subscr_ID ,
                   N_Sale_Item,
                 D_Date,
              N_Transaction_Code,
                 C_Agent_Name ,
             C_Agent_INN ,

             N_Amount,
             C_Notes
             from (
            select p.nump AS N_Code,
                   p.kodp S_Subscr_ID ,
                  case when  r.name ='Основная реализация'  or r.name = 'Неустойка в виде штрафа' then 231  else
                   case when  r.name ='Неустойка в виде пени'   then 110 else 111
                   end  end   as N_Sale_Item,
               case when s.dat_opl is not null then
            kg.kf_nyear( s.dat_opl) ||'-'||(case when LENGTH( kg.kf_nmonth(s.dat_opl))=1 then 0|| kg.kf_nmonth(s.dat_opl)
            else TO_CHAR(kg.kf_nmonth(s.dat_opl)) end)||'-'||
            (case when LENGTH( kg.kf_nday(s.dat_opl))=1 then 0|| kg.kf_nday(s.dat_opl)
             else TO_CHAR(kg.kf_nday(s.dat_opl)) end) else null end  AS  D_Date,
             s.check_num  N_Transaction_Code,
                case  when   b.name is null then 'Касса' else b.name end as C_Agent_Name ,
             b.inn C_Agent_INN ,

            sum (o.opl)  N_Amount,
             s.remark C_Notes ,sr.prim

            from kr_dogovor d,
              kr_payer p,
               tsr_opl o,
               SK_VID_REAL r ,
               sr_opl_bank s,
              sr_pach sr,
                ks_bank b,
                ks_bankpol k
                               where 1=1
                                AND d.tep_el + 0 = 4
                               AND d.tep_el_byt = 1
                               and o.dep= d.dep
                              AND  d.DEP = $Base->asuse_code_dep

            and d.pr_active in (0,2)
            and p.prizn_activ in (0,2)

            --and nvl(mp.kind,777) not in (1,2)
                               AND o.ym  =$date
                               and r.vid_real= o.vid_real
                               and o.vid_real<>9

                               and d.kodp=p.kodp
                               and s.kod_sr_pach=sr.kod_pach
                             and   s.kodp=d.kodp(+)
                 and k.kodb=b.kodb(+)
                 and s.kodbpol=k.kodbpol(+)
                 and o.kod_link= s.kod_link(+)
                 and o.num_opl= s.num_opl(+)
                and o.kod_type_opl in (0,1)
               and  (o.remark not  like 'фиктивная%' or o.remark is null)
             group by p.nump ,
                   p.kodp  ,
                  case when  r.name ='Основная реализация'  or r.name = 'Неустойка в виде штрафа' then 231  else
                   case when  r.name ='Неустойка в виде пени'   then 110 else  111
                   end  end   ,
               case when s.dat_opl is not null then
            kg.kf_nyear( s.dat_opl) ||'-'||(case when LENGTH( kg.kf_nmonth(s.dat_opl))=1 then 0|| kg.kf_nmonth(s.dat_opl)
            else TO_CHAR(kg.kf_nmonth(s.dat_opl)) end)||'-'||
            (case when LENGTH( kg.kf_nday(s.dat_opl))=1 then 0|| kg.kf_nday(s.dat_opl)
             else TO_CHAR(kg.kf_nday(s.dat_opl)) end) else null end ,
             s.check_num  ,
                case  when   b.name is null then 'Касса' else b.name end,
             b.inn,
             s.remark,sr.prim)
            where prim not like '%ЕРИЦ%' and c_agent_name not like '%ЕРИЦ%'
             order by 1
            ";
        }

        /**
         * Начисление ASUSE (Oracle)
         * @param string $date
         * @param BaseRegion $Base
         * @return string
         */
        protected final static function calculateAsuseOracle(string $date, BaseRegion $Base): string {
            return "SELECT * FROM (

SELECT x2x.lic as \"lic\",
       x2x.lic as \"C_Code_Subscr\",
       pay.KF_ADRESS_O AS \"C_Address\",
       pay.ALLNAME AS \"FIO\",
       x2x.in_saldo_2 as \"N_Debt_Amount\",
       x2x.nach_2 AS \"N_Charge_Amount\",
       pay.oplata as \"oplata\",
       (x2x.in_saldo_2+x2x.nach_2)-(CASE WHEN pay.oplata IS NULL then 0 ELSE pay.oplata end) AS \"saldo_end\" FROM (
SELECT
  xxx.N_Code as lic,
  case WHEN ROUND(SUM(xxx.N_DEBIT),2) IS NULL THEN 0 ELSE  ROUND(SUM(xxx.N_DEBIT),2) END  as in_saldo_2,  -- входящее сальдо
  CASE WHEN ROUND(SUM(xxx.N_CALC_SUM),2) is NULL then 0 ELSE ROUND(SUM(xxx.N_CALC_SUM),2) END  AS nach_2       -- начислено

  FROM (
select * from (select
p.nump N_Code ,
p.kodp S_Subscr_ID,
 case when   kint.name ='Суточная' and   acc.vid_t in (25,-26) then 231  else
    case when   kint.name ='2-х зонн. день' and   acc.vid_t in (25,-26) then 232 else
   case when   kint.name ='2-х зонн. ночь' and   acc.vid_t in (25,-26) then 233  else
   case when   kint.name ='Суточная' and   acc.vid_t in (-37) then 2314  else
 case when   kint.name ='Суточная' and  acc.vid_t in (35,-36) then 1231  else
    case when   kint.name ='2-х зонн. день' and   acc.vid_t in (35,-36) then 1232 else
   case when   kint.name ='2-х зонн. ночь' and   acc.vid_t in (35,-36) then 1233  else
   231 end end end  end end end end as N_Sale_Item,

  replace ( acc.ym, '.','')  N_Period,
case when acc.vid_t in (35, -36) then 2 else 1 end as N_Calc_Type,
DECODE(
            NVL(
               t_byt_priem.get_real_calctype(pr.calctypen,
                                              pr.calctype_rasch),
               pr.calctypen
            ),
            0,
           2,
            null,
            2,
            6,
            2,
            12,
            2,
             8,
            2,
            7,

            1,
            9,
            1,
            1,
            3,
            11,
            4
         ) N_Calc_Method,
sum(case when acc.rym is null and  acc.vid_t not in (141)  then acc.cust  else 0 end)  N_Quantity,
  t.name C_Tariff_Name,
nvl(  acc.price, 0) N_Tariff,
  0 N_Debit,
sum(case when acc.rym is null and  acc.vid_t not in (141) then acc.nachisl  else 0 end)  N_Calc,
 case when acc.vid_t = -26 then 1.5  else null end as N_Coeff,

sum( case when acc.vid_t = -26 then acc.nachisl  else null end) as N_Calc_Koeff_more,


sum(case when acc.rym is not null or acc.vid_t  in (141)then acc.nachisl  else null end) as N_Recalc_Sum,
substr(TO_CHAR(
 wm_concat(distinct
   r.NAME
)
), 1, 100) C_Recalc_reason,
--sum(acc.nachisl)  N_Calc_Sum,
sum( CASE when acc.vid_t in (-37) then 0 ELSE acc.nachisl END)  N_Calc_Sum,
nvl(rd.rd, 0) N_Quantity_Sum,
replace((select
 substr(TO_CHAR(wm_concat(distinct  n2.rasx)), 1, 100)
   FROM
  ts_mop_const_rasx n2,
  hr_point_rasx_obj  n4, kr_object ob
  where 1 = 1
and n4.kod_mop_const_rasx_name = n2.kod_mop_const_rasx_date(+)
and n4.dat_po is null
and  n4.kod_obj(+) = ob.kod_obj
and ob.kodd = h.kod_r),'.',',')  N_Norm_Soi,

max(T_V_COUNT.Normativ(d.kod_dog, pr.kod_point, kg.ym_last_day(acc.ym)))N_Norm,
nvl(h.kod_r, h.kodd) S_House_ID,
null

N_Rec_Debt


from tnr_account acc, kr_dogovor d,    ks_tarif t, k_house h,
    tnr_priem pr, ss_vid_recalc r,   kr_payer p, kk_interval kint,
    (select  nvl(sum(nd.out), 0) as rd,  ob.kodd
 from tnr_priem_house nd, hr_point po,kr_object ob
 where nd.ym =".$date."
 and po.kod_point = nd.kod_point
  and po.nagruz_type not  in(4)
  and ob.kod_obj = po.kod_obj
  group by ob.kodd  ) rd
where 1 = 1
and acc.ym =".$date."
 and acc.vid_t not in (48, 47)

and acc.kod_dog = d.kod_dog
and d.kodp = p.kodp
and pr.kod_priem(+) = acc.kod_priem
and pr.num_priem(+) = acc.num_priem
AND p.kod_d = h.kodd
  and acc.tarif = t.tarif
   and t.kodinterval = kint.kodinterval(+)
   AND acc.vid_recalc = r.vid_recalc(+)
   and nvl(h.kod_r , h.kodd)= rd.kodd(+)
    and(d.dep in (".$Base->asuse_code_dep.")  or  nk_adress.kf_address(5, p.kod_d)  like '%Костромское%'
  or  nk_adress.kf_address(5, p.kod_d)  like '%Пионеры%')
group by  p.nump  ,h.kod_r,nvl(rd.rd, 0),
nvl(h.kod_r, h.kodd) ,d.kod_dog,
p.kodp ,
 replace(acc.ym, '.', '') ,
 case when kint.name = 'Суточная' and acc.vid_t in (25, -26) then 231  else
    case when kint.name = '2-х зонн. день' and acc.vid_t in (25, -26) then 232 else 
   case when kint.name = '2-х зонн. ночь' and acc.vid_t in (25, -26) then 233  else
   case when   kint.name ='Суточная' and   acc.vid_t in (-37) then 2314  else
 case when kint.name = 'Суточная' and acc.vid_t in (35, -36) then 1231  else
    case when kint.name = '2-х зонн. день' and acc.vid_t in (35, -36) then 1232 else 
   case when kint.name = '2-х зонн. ночь' and acc.vid_t in (35, -36) then 1233  else 
    231 end end end  end end end end ,
   DECODE (
            NVL(
              t_byt_priem.get_real_calctype(pr.calctypen,
                                             pr.calctype_rasch),
              pr.calctypen
           ),
           0,
          2, 
           null,
           2, 
           6,
           2, 
           12,
           2, 
            8,
           2, 
           7,

           1, 
           9,
           1, 
           1,
           3, 
           11,
           4
         ) ,
         case when acc.vid_t in (35, -36) then 2 else 1 end,
  t.name ,
  acc.price,
 case when acc.vid_t = -26 then 1.5  else null end

   union all
   SELECT
 p.nump N_Code,
p.kodp S_Subscr_ID,
231  N_Sale_Item,
 replace( ".$date.", '.', '')  N_Period, 


 1  N_Calc_Type,
null  N_Calc_Method,
null  N_Quantity,
null C_Tariff_Name,
0 N_Tariff,
 
        sum(NVL((SELECT SUM(acc.nachisl)
                   FROM tnr_account acc, SK_VID_REAL r, sk_nachisl n
                  WHERE acc.kod_dog = d.kod_dog
                  and(acc.dep in (".$Base->asuse_code_dep.")  or  nk_adress.kf_address(5, p.kod_d)  like '%Костромское%'
  or  nk_adress.kf_address(5, p.kod_d)  like '%Пионеры%')
                and n.vid_real = r.vid_real
 and n.vid_t = acc.vid_t
 and r.vid_real in (2, 4)
                  
                   AND acc.ym < ".$date."), 0)
         - NVL((SELECT SUM(o.opl)
                   FROM tsr_opl o
                  WHERE o.kod_dog = d.kod_dog
                 and  o.vid_real in (2, 4)
                  and(o.dep in (".$Base->asuse_code_dep.")  or  nk_adress.kf_address(5, p.kod_d)  like '%Костромское%'
  or  nk_adress.kf_address(5, p.kod_d)  like '%Пионеры%')
                  AND o.ym < ".$date."
                  ), 0)) N_Debit,
null  N_Calc, 
null as N_Coeff, 
null as N_Calc_Koeff_more,
null as N_Recalc_Sum,
null C_Recalc_reason,
null  N_Calc_Sum,
null N_Quantity_Sum,
null  N_Norm_Soi,
null N_Norm,
nvl(h.kod_r, h.kodd) S_House_ID,
0 N_Rec_Debt--,


FROM kr_payer p, k_house h,   kr_dogovor d
   WHERE 1 = 1
     AND d.tep_el + 0 = 4
     AND d.tep_el_byt = 1
     AND d.kodp = p.kodp
   and(d.dep in (".$Base->asuse_code_dep.")  or  nk_adress.kf_address(5, p.kod_d)  like '%Костромское%'
  or  nk_adress.kf_address(5, p.kod_d)  like '%Пионеры%')
       AND p.kod_d = h.kodd

   group by  p.nump  ,
p.kodp,replace( ".$date.", '.', ''),
  nvl(h.kod_r, h.kodd)
  ) t
  where 1 = 1
  and(t.N_Debit <> 0  or   t.n_calc_sum <> 0)

union all
select* from(

select
 p.nump N_Code,
p.kodp S_Subscr_ID,
110 N_Sale_Item,
  replace(acc.ym, '.', '')  N_Period, 
3  N_Calc_Type,
null  N_Calc_Method,
null  N_Quantity,
null  C_Tariff_Name,
null  N_Tariff,
  0 N_Debit,
sum(case when acc.rym is null then acc.nachisl  else 0 end)  N_Calc, 
null as N_Coeff,
null as N_Calc_Koeff_more,
sum(case when acc.rym is not null then acc.nachisl  else null end) as N_Recalc_Sum,
substr(TO_CHAR(
 wm_concat(distinct
   r.NAME
)
), 1, 100) C_Recalc_reason,
--sum(acc.nachisl)  N_Calc_Sum,
sum( CASE when acc.vid_t in (-37) then 0 ELSE acc.nachisl END)  N_Calc_Sum,
null  N_Quantity_Sum,
null   N_Norm_Soi,
null N_Norm,
nvl(h.kod_r, h.kodd) S_House_ID,
0 N_Rec_Debt--,
 

from tnr_account acc, kr_dogovor d,   k_house h,
   ss_vid_recalc r,   kr_payer p
where 1 = 1
and acc.ym =".$date."
 and acc.vid_t  in (48, 47)
and acc.kod_dog = d.kod_dog
and d.kodp = p.kodp
AND p.kod_d = h.kodd
AND acc.vid_recalc = r.vid_recalc(+)
 and(d.dep in (".$Base->asuse_code_dep.")  or  nk_adress.kf_address(5, p.kod_d)  like '%Костромское%'
  or  nk_adress.kf_address(5, p.kod_d)  like '%Пионеры%')

group by  p.nump  ,h.kod_r,
nvl(h.kod_r, h.kodd) ,d.kod_dog,
p.kodp ,
 replace(acc.ym, '.', '')
 union all
 SELECT
 p.nump N_Code,
p.kodp S_Subscr_ID,
110 N_Sale_Item,
  replace( ".$date.", '.', '')  N_Period, 

 1  N_Calc_Type,
null  N_Calc_Method,
null  N_Quantity,
null C_Tariff_Name,
0 N_Tariff,
 
        sum(NVL((SELECT SUM(acc.nachisl)
                   FROM tnr_account acc, SK_VID_REAL r, sk_nachisl n
                  WHERE acc.kod_dog = d.kod_dog
                  and(acc.dep in (".$Base->asuse_code_dep.")  or  nk_adress.kf_address(5, p.kod_d)  like '%Костромское%'
  or  nk_adress.kf_address(5, p.kod_d)  like '%Пионеры%')
                and n.vid_real = r.vid_real
 and n.vid_t = acc.vid_t
 and r.vid_real in (7)
             
                   AND acc.ym < ".$date."), 0)
         - NVL((SELECT SUM(o.opl)
                   FROM tsr_opl o
                  WHERE o.kod_dog = d.kod_dog
                 and  o.vid_real in (7)
                  and(o.dep in (".$Base->asuse_code_dep.")  or  nk_adress.kf_address(5, p.kod_d)  like '%Костромское%'
  or  nk_adress.kf_address(5, p.kod_d)  like '%Пионеры%')
                  AND o.ym < ".$date."
                  ), 0)) N_Debit,
null  N_Calc, 
null as N_Coeff,
null as N_Calc_Koeff_more,
null as N_Recalc_Sum,
null C_Recalc_reason,
null  N_Calc_Sum,
null N_Quantity_Sum,
null  N_Norm_Soi,
null N_Norm,
nvl(h.kod_r, h.kodd) S_House_ID,
0 N_Rec_Debt--,

FROM kr_payer p, k_house h,  kr_dogovor d


   WHERE 1 = 1
     AND d.tep_el + 0 = 4
     AND d.tep_el_byt = 1
     AND d.kodp = p.kodp
   and(d.dep in (".$Base->asuse_code_dep.")  or  nk_adress.kf_address(5, p.kod_d)  like '%Костромское%'
  or  nk_adress.kf_address(5, p.kod_d)  like '%Пионеры%')
       AND p.kod_d = h.kodd

   group by  p.nump  , replace( ".$date.", '.', '') , 
p.kodp,
  nvl(h.kod_r, h.kodd)) t
where 1 = 1
 and(t.N_Debit <> 0  or   t.n_calc_sum <> 0)
-- )

order by 1) xxx
  GROUP BY xxx.N_Code) x2x

  INNER JOIN (
  SELECT distinct(kp1.NUMP),o1.YM,kp1.ALLNAME,kh.KF_ADRESS_O,

(SELECT SUM(o.OPL) FROM tsr_opl o   
  INNER JOIN KR_DOGOVOR kd
  ON o.KOD_DOG = kd.KOD_DOG
  INNER JOIN KR_PAYER kp ON kd.KODP = kp.KODP
  INNER JOIN K_HOUSE kh ON kp.KOD_D = kh.KODD
  WHERE o.YM='".$date."' AND o.VID_REAL IN (2,4,7,3,9) AND o.TEP_EL =1
  AND kd.PR_ACTIVE IN (0,2)

  AND kp.KODP = kp1.kodp) AS oplata

FROM tsr_opl o1  
  INNER JOIN KR_DOGOVOR kd1
  ON o1.KOD_DOG = kd1.KOD_DOG
  INNER JOIN KR_PAYER kp1 ON kd1.KODP = kp1.KODP
  INNER JOIN K_HOUSE kh ON kp1.KOD_D = kh.KODD
  WHERE o1.YM='".$date."' AND o1.VID_REAL IN (2,4,7,3,9) AND o1.TEP_EL =1
  AND kd1.PR_ACTIVE IN (0,2)  
  ) pay
  ON pay.NUMP = x2x.lic

  ORDER BY x2x.lic)";
        }

        /**
         * показание приборов!!!!!!!!!!!!!!!!
         */
        protected final static function query6() {
            return "SELECT distinct 
        p.nump AS N_Code,
       p.kodp S_Subscr_ID ,
    case when   kint.name ='—уточна¤'  then 231  else
    case when   kint.name ='2-х зонн. день'  then 232 else 
   case when   kint.name ='2-х зонн. ночь'  then 233
    else 231 end end end  as N_Sale_Item,         
       replace ( pr.ym, '.','')N_Period,     
        pu.nom_pu C_Serial_Number ,     
       hg_pasp_pu_r.name_pu (puu.kod_tippu, 2) as C_Device_Type,  
         pu.kod_point_pu S_Device_ID ,
case when pu.dat_s is not null then TO_CHAR(pu.dat_s,'YYYY-MM-DD') else  null end as D_Date_Begin,
case when pu.dat_po >kg.ym_last_day ( pr.ym) or pu.dat_po is null  then null else TO_CHAR(pu.dat_po,'YYYY-MM-DD') end as  D_Date_End,
case when   kint.name ='—уточна¤' and   acc.vid_t in (25,-26) then 0  else
case when   kint.name ='2-х зонн. день' and   acc.vid_t in (25,-26) then 4 else 
case when   kint.name ='2-х зонн. ночь' and   acc.vid_t in (25,-26) then 3
else 0 end end end    as N_Time_Zone, 

to_char(nvl(pr5.dddt,pu.dat_s),'YYYY-MM-DD') as D_Date_Pre,
case when pr5.readlast is null then pr.readprev else pr5.readlast end n_value_prev,
         
case when pr.calctype_rasch=0 then case when pr.dat_byt is not null then TO_CHAR(pr.dat_byt,'YYYY-MM-DD') else  null end else  null end as D_Date_Cur, 
case when pr.calctype_rasch=0 then pr.readlast else null end as N_Value_Cur,      


( select sum (acc1.cust) from   tnr_account acc1
   where 1=1  
    and  acc1.kod_dog=acc.kod_dog
      and acc1.kod_priem=acc.kod_priem
    and acc1.num_priem=acc.num_priem
    and acc1.ym=acc.ym
    and acc1.tarif=acc.tarif
    )  N_Quantity ,
          
 case when    nvl(a.kvartal_op,1)>=4
     then  
     ( case when kg.ym_last_day(nvl(a.god_op,1960)|| '.' || 3*4) is not null then 
kg.kf_nyear( kg.ym_last_day(nvl(a.god_op,1960)|| '.' || 3*4)) ||'-'||
(case when LENGTH( kg.kf_nmonth(kg.ym_last_day(nvl(a.god_op,1960)|| '.' || 3*4)))=1 
then 0|| kg.kf_nmonth(kg.ym_last_day(nvl(a.god_op,1960)|| '.' || 3*4))
else TO_CHAR(kg.kf_nmonth(kg.ym_last_day(nvl(a.god_op,1960)|| '.' || 3*4))) end)||'-'||
(case when LENGTH( kg.kf_nday(kg.ym_last_day(nvl(a.god_op,1960)|| '.' || 3*4)))=1 
then 0|| kg.kf_nday(kg.ym_last_day(nvl(a.god_op,1960)|| '.' || 3*4))
 else TO_CHAR(kg.kf_nday(kg.ym_last_day(nvl(a.god_op,1960)|| '.' || 3*4))) end)end )   
 else kg.kf_nyear( kg.ym_last_day (nvl(a.god_op,1960)|| '.' || 0||3*nvl(a.kvartal_op,1))) ||'-'||
(case when LENGTH( kg.kf_nmonth(kg.ym_last_day (nvl(a.god_op,1960)|| '.' || 0||3*nvl(a.kvartal_op,1))))=1 then
 0|| kg.kf_nmonth(kg.ym_last_day (nvl(a.god_op,1960)|| '.' || 0||3*nvl(a.kvartal_op,1)))
else TO_CHAR(kg.kf_nmonth(kg.ym_last_day (nvl(a.god_op,1960)|| '.' || 0||3*nvl(a.kvartal_op,1)))) end)||'-'||
(case when LENGTH( kg.kf_nday(kg.ym_last_day (nvl(a.god_op,1960)|| '.' || 0||3*nvl(a.kvartal_op,1))))=1 
then 0|| kg.kf_nday(kg.ym_last_day (nvl(a.god_op,1960)|| '.' || 0||3*nvl(a.kvartal_op,1)))
 else TO_CHAR(kg.kf_nday(kg.ym_last_day (nvl(a.god_op,1960)|| '.' || 0||3*nvl(a.kvartal_op,1)))) end)     
         end      as D_Date_Verification

, case when nvl(ini.rkoeff,1) <= 1 then 1 else ini.rkoeff end  as k

FROM kr_dogovor d,
           
       kr_org o,
       kr_payer p,
       kr_numobj n,
       kr_object o,
       hr_point po,
       hr_point_pu pu,
       hr_pu_u puu,   
       TV_POINT_PU a, 
       hr_point_en en,
       hr_point_ini ini,
       hr_point_tar tar,
       ks_tarif t,
       tnr_priem pr,
       tnr_account acc,
      kk_interval kint,

( Select pr3.kod_point,
        pr3.kod_point_ini,
        pr3.dat_byt,
        pr3.calctype_rasch,
        pr3.rnk,
        pr4.dat_byt dddt,
        pr4.calctype_rasch,
        pr4.rnk1,
        pr4.readlast
        from 

(select sss.*,rank() over(partition by kod_point,kod_point_ini order by dat_byt desc) rnk from 
        (select kod_point,kod_point_ini,readlast,dat_byt,calctype_rasch from tnr_priem where ym=$date order by 1,4) sss where calctype_rasch<>11) pr3 
left outer join (select sss.*,rank() over(partition by kod_point ,kod_point_ini order by dat_byt desc) rnk1 from 
                 	(select kod_point,kod_point_ini,readlast,dat_byt,calctype_rasch from tnr_priem where ym<=$date order by 1,4) sss where calctype_rasch=0) pr4

on
    pr3.kod_point=pr4.kod_point
    and pr3.kod_point_ini=pr4.kod_point_ini
    and pr3.rnk=1 and pr4.rnk1=case when pr3.calctype_rasch=0 then 2
                                else 1
                                end                   
)pr5

 WHERE p.kodp = d.kodp
 and pr.kod_point_ini=ini.kod_point_ini(+)
 and pr.kod_point=pu.kod_point(+)
and pr5.kod_point=pr.kod_point
and pr5.kod_point_ini=pr.kod_point_ini

--and pr2.readlast=pr.readprev
 and t.tarif=tar.tarif
--:dep
  
 AND d.tep_el + 0 = 4
   AND d.tep_el_byt = 1
   AND d.kod_dog = n.kod_dog
   AND n.kod_obj = o.kod_obj
   AND po.kod_obj = o.kod_obj
   AND po.kod_point = pu.kod_point
   AND pu.kod_point_pu = en.kod_point_pu
   AND en.kod_point_en(+) = ini.kod_point_en
    AND tar.kod_numobj = n.kod_numobj 
   AND ini.kod_point_tar = tar.kod_point_tar 
   and t.kodinterval=kint.kodinterval(+)
   and pu.kod_pu_u=puu.kod_pu_u(+)       
    and a.kod_point_pu= pu.kod_point_pu
   and a.nom_pu= pu.nom_pu                      
   and   pr.kod_priem IS NOT NULL 
  and d.kod_dog=acc.kod_dog
   and acc.kod_priem=pr.kod_priem
   and acc.num_priem= pr.num_priem
   and acc.ym=pr.ym(+)
   and acc.tarif=t.tarif
   and pr.ym = $date
   AND d.podr = o.kodp   
   and pr.calctype in (0,12,6)
--  and p.nump='170105004'
 and d.pr_active in (0,2)
and p.prizn_activ in (0,2)
--and nvl(mp.kind,777) not in (1,2)
  order by 1,5,6";
        }
    }
}
