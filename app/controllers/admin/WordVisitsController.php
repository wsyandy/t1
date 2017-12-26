<?php

namespace admin;

class WordVisitsController extends BaseController
{
    #数据列表
    public function indexAction()
    {
        $page = $this->params('page');
        $visit_at = $this->params('visit_at', date('Y-m-d'));
        $visit_at_end = $this->params('visit_at_end', date('Y-m-d'));

        $begin = strtotime($visit_at);
        $end = strtotime($visit_at_end);

        $sems = \WordVisits::find(['columns' => 'distinct sem']);
        $sem = $this->params('sem');
        $export = $this->params('export');
        $cond = [
            'conditions' => 'visit_at >= :begin: and visit_at <= :end:',
            'bind' => ['begin' => $begin, 'end' => $end],
            'order' => 'visit_num desc'
        ];

        if ($sem) {
            $cond['conditions'] .= " and sem = :sem:";
            $cond['bind']['sem'] = $sem;
        }

        if ($export) {
            $per_page = \WordVisits::count($cond);
            $page = 1;
            $word_visits = \WordVisits::findPagination($cond, $page, $per_page);
            $titles = ["时间", "渠道", "关键词", "访问量", "下载量"];
            $data = [];
            foreach ($word_visits as $word_visit) {
                $word = $word_visit->word;
                if (0 == strpos($word, '=')) {
                    $word = str_replace('=', '', $word);
                }
                $data[] = [$word_visit->visit_at_date, $word_visit->sem, strval($word),
                    $word_visit->visit_num, $word_visit->down_num
                ];
            }

            $temp_name = 'word_visits_' . date('Ymd') . '_' . time() . '.xls';
            info($titles, $data, $per_page);
            $uri = writeExcel($titles, $data, $temp_name, true);
            if ($uri) {
                $export_history = new \ExportHistories();
                $export_history->operator_id = $this->currentOperator()->id;
                $export_history->name = 'sem关键词';
                $export_history->table_name = 'WordVisits';
                $export_history->conditions = json_encode(fetch($cond, 'bind'), JSON_UNESCAPED_UNICODE);
                $export_history->download_num = 0;
                $export_history->file = $uri;
                $export_history->save();

                $this->response->redirect('/admin/export_histories/download?id=' . $export_history->id);
            }
            $this->view->disable();
            return;
        }

        $word_visits = \WordVisits::findPagination($cond, $page);
        $this->view->word_visits = $word_visits;
        $this->view->visit_at = $visit_at;
        $this->view->visit_at_end = $visit_at_end;
        $this->view->sems = $sems;
        $this->view->sem = $sem;
    }
}
