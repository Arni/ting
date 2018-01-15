<?php

class TingClientObjectRecommendationRequest extends TingClientRequest {
  const GENDER_MALE = 'male';
  const GENDER_FEMALE = 'female';

  protected $isbn;
  protected $numResults;
  protected $gender;
  protected $minAge;
  protected $maxAge;
  protected $fromDate;
  protected $toDate;
  protected $faust;

  public function getFaust() {
    return $this->faust;
  }

  public function setFaust($faust) {
    $this->faust = $faust;
  }

  
  public function getIsbn() {
    return $this->isbn;
  }

  public function setIsbn($isbn) {
    $this->isbn = $isbn;
  }

  function getNumResults() {
    return $this->numResults;
  }

  function setNumResults($numResults) {
    $this->numResults = $numResults;
  }

  public function getGender() {
    return $this->gender;
  }

  public function setGender($gender) {
    $this->gender = $gender;
  }

  public function getAge() {
    return array($this->minAge, $this->maxAge);
  }

  public function setAge($minAge, $maxAge) {
    $this->minAge = $minAge;
    $this->maxAge = $maxAge;
  }

  public function getDate() {
    return array($this->fromDate, $this->toDate);
  }

  public function setDate($fromDate, $toDate) {
    $this->fromDate = $fromDate;
    $this->toDate = $toDate;
  }

  protected function getRequest() {
    $this->setParameter('action', 'adhlRequest');

    if ($this->isbn) {
      $this->setParameter('id', array('isbn' => $this->isbn));
    } else if ($this->faust) {
      $this->setParameter('id', array('faust' => $this->faust));
    }

    if ($this->numResults) {
      $this->setParameter('numRecords', $this->numResults);
    }

    if ($this->gender) {
      switch ($this->gender) {
        case TingClientObjectRecommendationRequest::GENDER_MALE:
          $gender = 'm';
          break;
        case TingClientObjectRecommendationRequest::GENDER_FEMALE:
          $gender = 'k';
      }
      $this->setParameter('gender', $gender);
    }

    if ($this->minAge || $this->maxAge) {
      $minAge = ($this->minAge) ? $this->minAge : 0;
      $maxAge = ($this->maxAge) ? $this->maxAge : 100;
      $this->setParameter('minAge', $minAge);
      $this->setParameter('maxAge', $maxAge);
    }

    if ($this->fromDate || $this->toDate) {
      $this->setParameter('from', $this->fromDate);
      $this->setParameter('to', $this->toDate);
    }
    return $this;
  }

  public function mygetValue($object) {
    if (is_array($object)) {
      return array_map(array('RestJsonTingClientRequest', 'getValue'), $object);
    } else {
      return $this->mygetBadgerFishValue($object, '$');
    }
  }
  
  public function mygetBadgerFishValue($badgerFishObject, $valueName) {
    $properties = get_object_vars($badgerFishObject);

    if (isset($properties[$valueName])) {
      $value = $properties[$valueName];
      if (is_string($value)) {
        //some values contain html entities - decode these
        $value = html_entity_decode($value, ENT_COMPAT, 'UTF-8');
      }
      return $value;
    } else {
      return NULL;
    }
  }

  public function processResponse(stdClass $response) {
    if (isset($response->error)) {
      throw new TingClientException('Error handling recommendation request: ' . $response->error);
    }
    $recommendations = array();
    if (isset($response->adhlResponse->record)) {
      foreach ($response->adhlResponse->record as $record) {
        $recommendation = new TingClientObjectRecommendation();
        if ($id = $this->mygetValue($record->recordId)) {
          $id = explode('|', $id, 2);
          $recommendation->localId = $id[0];
          $recommendation->ownerId = (isset($id[1])) ? $id[1] : null;
          if ($title = $record->title) {
            $recommendation->title = $this->mygetValue($title[0]);
          }
          if ($creator = $record->creator) {
            $recommendation->creator = $this->mygetValue($creator[0]);
          }
          
          $recommendations[] = $recommendation;
        }
      }
    }
    return $recommendations;
  }

}

