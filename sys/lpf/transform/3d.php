<?php

/**
 * Matrix Math Routines 
 *
 * Based on tutorial at:
 *   http://www.inversereality.org/tutorials/graphics%20programming/3dwmatrices.html
 *
 *
 *
 *
 *
 *
 */
 
class Point3D {
    public $lx, $ly, $lz, $lt;
    public $wx, $wy, $wz, $wt;
    public $ax, $ay, $az, $at;
    public $sx, $sy, $sz, $st;
}

class Matrix3D {
    public $matrix = array(4,4);
    public function __construct() {
        $this->reset();
    }
    public function reset() {
        for ($i = 0; $i < 4; $i++) {
            for ($j = 0; $j < 4; $j++) {
                $this->matrix[$i][$j] = 0;
            }
        }
    }
    public function identity() {
        $this->reset();
        $matrix[0][0] = 1;
        $matrix[1][1] = 1;
        $matrix[2][2] = 1;
        $matrix[3][3] = 1;
    }
    public function copy(Matrix3D $newm) {
        $temp = new Matrix3D();
        for ($i = 0; $i < 4; $i++) {
            for ($j = 0; $j < 4; $j++) {
                $temp->matrix[$i][$j] = (
                    ($this->matrix[$i][0]*$newm->matrix[0][$j]) +
                    ($this->matrix[$i][1]*$newm->matrix[1][$j]) +
                    ($this->matrix[$i][2]*$newm->matrix[2][$j]) +
                    ($this->matrix[$i][3]*$newm->matrix[3][$j]) 
                );
            }
        }
        for($i = 0; $i < 4; $i++) {
            $this->matrix[$i][0] = $temp->matrix[$i][0];
            $this->matrix[$i][1] = $temp->matrix[$i][1];
            $this->matrix[$i][2] = $temp->matrix[$i][2];
            $this->matrix[$i][3] = $temp->matrix[$i][3];
        }
    }
    public function mult(Matrix3D $m1, Matrix3D $m2) {
    }
}

class Lpf3DTransform {
    public $matrix = new Matrix3D();
    public $rmat = new Matrix3D();
    public $rmatrix = new Matrix3D();
    public $objectmatrix = new Matrix3D();
    public $local = 0;
    public function __construct() { }
    public function __destruct() { }
    public function init() { }
    public function translate($x,$y,$z) { }
    public function rotate($x,$y,$z) {
        $this->rmatrix->identity();
        $this->rmat->identity();
        
        $this->rmat->matrix[1][1] = cos($x);    $this->rmat->matrix[1][2] = sin($x);
        $this->rmat->matrix[2][1] = -sin($x);   $this->rmat->matrix[2][2] = cos($x);
        $rmatrix->mult($this->rmatrix,$this->rmat); $this->rmat->identity();
        
        $this->rmat->matrix[0][0] = cos($y);    $this->rmat->matrix[0][2] = sin($y);
        $this->rmat->matrix[2][0] = sin($y);    $this->rmat->matrix[2][2] = cos($y);
        $rmatrix->mult($this->rmatrix,$this->rmat); $this->rmat->identity();

        $this->rmat->matrix[0][0] = cos($z);    $this->rmat->matrix[0][1] = sin($z);
        $this->rmat->matrix[1][0] = -sin($z);   $this->rmat->matrix[1][1] = cos($z);
        $rmatrix->mult($this->rmatrix,$this->rmat); $this->rmat->identity();

        if ($this->local) {
            $this->objectmatrix->identity();
            $this->objectmatrix->copy($this->rmatrix);
        } else {
            $this->matrix->copy($this->rmatrix);
        }
    }
    public function scale($s) {
        $this->rmat->identity();
        $this->rmat->matrix[0][0] = $s;
        $this->rmat->matrix[1][1] = $s;
        $this->rmat->matrix[2][2] = $s;

        if ($this->local) {
            $this->objectmatrix->copy($this->rmatrix);
        } else {
            $this->matrix->copy($this->rmatrix);
        }
    }
    public function changeLocalObject(Point3D $point) { }
    public function changeObjectPoint(Point3D $point) { }
}





class Transform3D_transformation extends TransformationBase {
    $this->addParameter('rotx',0, lpf::float(-16000,16000));
    $this->addParameter('roty',0, lpf::float(-16000,16000));
    $this->addParameter('rotz',0, lpf::float(-16000,16000));



}
