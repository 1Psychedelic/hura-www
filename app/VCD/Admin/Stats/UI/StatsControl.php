<?php

namespace VCD\Admin\Stats\UI;

use Hafo\Persona\CzechPersonalId;
use Hafo\Persona\Gender;
use Hafo\Persona\HumanAge;
use Psr\Container\ContainerInterface;
use Nette\Application\UI\Control;
use Nette\Database\Context;
use Nette\Utils\Arrays;

class StatsControl extends Control
{
    const TAB_ATTENDATION = 'attendation';
    const TAB_USERS = 'users';
    const TAB_CALENDAR = 'calendar';
    const TAB_AGE = 'age';
    const TAB_SUBSCRIBERS = 'subscribers';
    const TAB_LOYALTY = 'loyalty';
    const TAB_AVERAGE_PRICE = 'average_price';

    private $container;

    private $tab;

    public function __construct(ContainerInterface $container, $year = null, $tab = self::TAB_ATTENDATION)
    {
        $this->container = $container;
        $this->tab = $tab;

        $this->onAnchor[] = function () use ($year, $tab) {
            $db = $this->container->get(Context::class);

            $this->template->year = $year;

            if ($tab === self::TAB_ATTENDATION) {
                $this->setupAttendation($year);
            } elseif ($tab === self::TAB_USERS) {
                $this->setupUsers($year);
            } elseif ($tab === self::TAB_CALENDAR) {
                $this->setupCalendar($year);
            } elseif ($tab === self::TAB_AGE) {
                $this->setupAge($year);
            } elseif ($tab === self::TAB_SUBSCRIBERS) {
                $this->setupSubscribers($year);
            } elseif ($tab === self::TAB_LOYALTY) {
                $this->setupLoyalty($year);
            } elseif ($tab === self::TAB_AVERAGE_PRICE) {
                $this->setupAveragePrice($year);
            }
        };
    }

    public function render()
    {
        $this->template->setFile(__DIR__ . '/' . $this->tab . '.latte');
        $this->template->render();
    }

    private function setupAveragePrice($year = null) {
        $db = $this->db();

        $selection = $db->table('vcd_event')->where('visible = 1')->order('starts ASC');
        if ($year !== null) {
            $selection->where('YEAR(starts)', $year);
        }

        $data = [];
        foreach ($selection as $row) {
            $key = $row['id'] . ' ' . $row['name'];

            $sumChildren = 0;
            $sumPrice = 0;
            foreach ($row->related('vcd_application', 'event')->where('applied_at IS NOT NULL AND accepted_at IS NOT NULL AND rejected_at IS NULL AND canceled_at IS NULL') as $application) {
                $sumChildren += $application->related('vcd_application_child')->count();
                $sumPrice += $application['price'];
            }

            if ($sumChildren === 0) {
                continue;
            }

            $data[$key] = round($sumPrice / $sumChildren, 2);
        }

        $this->template->averagePrice = $data;
    }

    private function setupLoyalty($year = null)
    {
        $db = $this->db();

        $selection = $db->table('vcd_event')->where('visible = 1')->order('starts ASC');
        //if ($year !== null) {
        //    $selection->where('YEAR(starts)', $year);
        //}

        $loyalty = [];
        $data = [];
        $new = [];
        $returning = [];
        $unused = [];
        foreach ($selection as $row) {
            $key = $row['id'] . ' ' . $row['name'];
            $new[$key] = 0;
            $returning[$key] = 0;
            $unused[$key] = 0;
            foreach ($row->related('vcd_application', 'event')->where('applied_at IS NOT NULL AND accepted_at IS NOT NULL AND rejected_at IS NULL AND canceled_at IS NULL') as $application) {
                foreach ($application->related('vcd_application_child') as $child) {
                    if ($child['child'] === null) {
                        $new[$key]++;
                    } else {
                        if (array_key_exists($child['child'], $loyalty)) {
                            $returning[$key]++;
                        } else {
                            $new[$key]++;
                            $loyalty[$child['child']] = true;
                        }
                    }
                }
            }
            if (($year !== null && $row['starts']->format('Y') !== $year) || ($new[$key] === 0 && $returning[$key] === 0)) {
                unset($new[$key], $returning[$key], $data[$key], $unused[$key]);
            } else {
                $data[$key] = $new[$key] + $returning[$key];
                $unused[$key] = max(0, $row['max_participants'] - ($new[$key] + $returning[$key]));
            }
        }

        $this->template->loyalty = $data;
        $this->template->loyaltyNew = $new;
        $this->template->loyaltyReturning = $returning;
        $this->template->loyaltyUnused = $unused;
    }

    private function setupAttendation($year = null)
    {
        $db = $this->db();

        $this->template->attendation = $this->getAttendation($year);

        $selection = $db->table('vcd_event')->where('visible = 1')->order('starts ASC');
        if ($year !== null) {
            $selection->where('YEAR(starts)', $year);
        }
        $data = [];
        $boys = [];
        $girls = [];
        $capacity = [];
        foreach ($selection as $row) {
            $key = $row['id'] . ' ' . $row['name'];
            $boys[$key] = 0;
            $girls[$key] = 0;
            $children = 0;
            foreach ($row->related('vcd_application', 'event')->where('applied_at IS NOT NULL AND accepted_at IS NOT NULL AND rejected_at IS NULL AND canceled_at IS NULL') as $application) {
                $children += count($application->related('vcd_application_child'));
                foreach ($application->related('vcd_application_child') as $child) {
                    if ($child['gender'] === Gender::MALE) {
                        $boys[$key]++;
                    } else {
                        $girls[$key]++;
                    }
                    /*$pid = new CzechPersonalId($child['personal_id']);
                    if($pid->gender() === Gender::MALE) {
                        $boys[$row['name']]++;
                    } else {
                        $girls[$row['name']]++;
                    }*/
                }
            }
            if ($children === 0) {
                unset($boys[$key], $girls[$key]);

                continue;
            }
            $capacity[$key] = max(0, $row['max_participants'] - ($boys[$key] + $girls[$key]));
            $data[$key] = $children;
        }
        $this->template->attendationByEvent = $data;
        $this->template->attendationByEventBoys = $boys;
        $this->template->attendationByEventGirls = $girls;
        $this->template->attendationCapacity = $capacity;

        $this->template->countApplications = $this->getCountApplications($year);
        $this->template->countApplicationChildren = $this->getCountApplicationChildren($year);
    }

    private function setupSubscribers($year = null)
    {
        $db = $this->db();

        $selection = $db->table('vcd_newsletter')->group('CONCAT(YEAR(added_at), "-", MONTH(added_at))')->order('added_at ASC')->select('COUNT(id) AS cnt, CONCAT(YEAR(added_at), "-", MONTH(added_at)) AS added');
        $users = $selection->fetchPairs('added', 'cnt');
        $data = [];
        $counter = 0;
        foreach ($users as $registered => $cnt) {
            list($regYear, $regMonth) = explode('-', $registered);
            $counter += $cnt;
            if ($year === null || (int)$regYear === (int)$year) {
                $data[$registered] = $counter;
            }
        }
        $this->template->subscribers = $data;
    }

    private function setupUsers($year = null)
    {
        $db = $this->db();

        $selection = $db->table('system_user')->group('CONCAT(YEAR(registered_at), "-", MONTH(registered_at))')->order('registered_at ASC')->select('COUNT(id) AS cnt, CONCAT(YEAR(registered_at), "-", MONTH(registered_at)) AS registered');
        $users = $selection->fetchPairs('registered', 'cnt');
        $data = [];
        $counter = 0;
        foreach ($users as $registered => $cnt) {
            list($regYear, $regMonth) = explode('-', $registered);
            $counter += $cnt;
            if ($year === null || (int)$regYear === (int)$year) {
                $data[$registered] = $counter;
            }
        }
        $this->template->users = $data;
    }

    private function setupCalendar($year = null)
    {
        $db = $this->db();

        $selection = null;
        if ($year === null) {
            $firstEvent = $db->table('vcd_event')->where('visible = 1')->order('starts ASC')->fetchField('starts');
            /** @var \DateTime $firstEvent */
            $start = (clone $firstEvent)->setDate($firstEvent->format('Y'), 1, 1)->setTime(0, 0, 0);
            $end = (new \DateTime)->setDate((new \DateTime)->format('Y'), 12, 31)->setTime(23, 59, 59);
            $selection = $db->table('vcd_event')->where('visible = 1')->order('starts ASC');
        } else {
            $start = (new \DateTime)->setDate($year, 1, 1)->setTime(0, 0, 0);
            $end = (new \DateTime)->setDate($year, 12, 31)->setTime(23, 59, 59);
            $selection = $db->table('vcd_event')->where('visible = 1 AND (YEAR(starts) = ? OR YEAR(ends) = ?)', [$year, $year]);
        }
        $oneDay = \DateInterval::createFromDateString('1 day');

        $countEmptyDays = 0;
        $countEventDays = 0;

        $eventData = [];
        foreach ($selection as $row) {
            /** @var \DatePeriod|\DateTime[] $period */
            $period = new \DatePeriod($row['starts'], $oneDay, $row['ends']);
            foreach ($period as $date) {
                $y = $date->format('Y');
                $m = $date->format('n');
                $d = $date->format('j');
                if (!array_key_exists($y, $eventData)) {
                    $eventData[$y] = [];
                }
                if (!array_key_exists($m, $eventData[$y])) {
                    $eventData[$y][$m] = [];
                }
                $eventData[$y][$m][$d] = $row;
                $countEventDays++;
            }
        }

        /** @var \DatePeriod|\DateTime[] $period */
        $period = new \DatePeriod($start, $oneDay, $end);
        $data = [];
        foreach ($period as $date) {
            $y = $date->format('Y');
            $m = $date->format('n');
            $d = $date->format('j');
            if (!array_key_exists($y, $data)) {
                $data[$y] = [];
            }
            if (!array_key_exists($m, $data[$y])) {
                $data[$y][$m] = [];
            }
            $data[$y][$m][$d] = $event = Arrays::get($eventData, [$y, $m, $d], null);
            if ($event === null) {
                $countEmptyDays++;
            }
        }
        $this->template->data = $data;
        $this->template->countEventDays = $countEventDays;
        $this->template->countEmptyDays = $countEmptyDays;
    }

    private function setupAge($year = null)
    {
        $db = $this->db();

        $selection = $this->selectApplications($year);
        $selection->group(':vcd_application_child.child');
        $selection->order('COUNT(:vcd_application_child.id) DESC');
        $selection->select('vcd_application.*,:vcd_application_child.gender AS gender,:vcd_application_child.date_born AS date_born');
        $boys = [];
        $girls = [];
        foreach (range(1, 18) as $i) {
            $boys[$i] = 0;
            $girls[$i] = 0;
        }
        foreach ($selection as $row) {
            $age = (new HumanAge(new \DateTime($row['date_born'])))->yearsAt($row->ref('vcd_event', 'event')['ends']);
            if ($row['gender'] === Gender::MALE) {
                $boys[$age]++;
            } elseif ($row['gender'] === Gender::FEMALE) {
                $girls[$age]++;
            }

            /*
            $pid = new CzechPersonalId($row['personal_id']);
            $age = $pid->age()->yearsAt($row->ref('vcd_event', 'event')['ends']);
            if($pid->gender() === Gender::MALE) {
                $boys[$age]++;
            } else if($pid->gender() === Gender::FEMALE) {
                $girls[$age]++;
            }*/
        }

        $this->template->boys = $boys;
        $this->template->girls = $girls;
    }

    private function getCountApplications($year = null)
    {
        return $this->selectApplications($year)->fetchField('COUNT(`id`)');
    }

    private function getCountApplicationChildren($year = null)
    {
        $children = 0;
        foreach ($this->selectApplications($year) as $application) {
            $children += $application->related('vcd_application_child', 'application')->fetchField('COUNT(`id`)');
        }

        return $children;
    }

    private function selectApplications($year = null)
    {
        $selection = $this->db()->table('vcd_application')->where('vcd_application.applied_at IS NOT NULL AND vcd_application.accepted_at IS NOT NULL AND vcd_application.rejected_at IS NULL AND vcd_application.canceled_at IS NULL');
        if ($year !== null) {
            $selection->where('YEAR(vcd_application.applied_at) = ?', $year);
        }

        return $selection;
    }

    private function getAttendation($year = null)
    {
        $selection = $this->selectApplications($year);
        $selection->group(':vcd_application_child.child');
        $selection->order('COUNT(:vcd_application_child.id) DESC');
        $selection->select(':vcd_application_child.child AS child_id,:vcd_application_child.name AS name, COUNT(:vcd_application_child.id) AS cnt');
        $data = [];
        foreach ($selection as $row) {
            $data[$row['child_id']] = [
                'name' => $row['child_id'] === null ? '(neregistrované)' : $row['name'],
                'count' => $row['cnt'],
            ];
        }

        return $data;
    }

    private function db()
    {
        return $this->container->get(Context::class);
    }
}
