@extends('layouts.app')

@section('title', 'Cronograma del Personal')

@section('content')
    <div class="container-fluid">
        <div class="text-start mb-3">
            <h3 class="pill-heading tituloVista">CRONOGRAMA DEL PERSONAL</h3>
        </div>

        <div class="container-fluid">
            
            <div class="card" style="border-radius:15px;">
                <div class="card-body">
                    <div class="cronograma-wrapper">

                        <div class="cronograma-table-wrapper">
                            <table id="tb_cronograma" class="cronograma-table table-striped">

                                <thead>
                                    <tr>
                                        <th class="sticky-col col-dni">DNI</th>
                                        <th class="sticky-col col-legajo">Legajo</th>
                                        <th class="sticky-col col-nombre">Empleado</th>
                                        <th class="sticky-col col-servicio">Servicio</th>
                                        <th class="sticky-col col-regimen">Régimen</th>

                                        <th>1<br><small>L</small></th>
                                        <th>2<br><small>M</small></th>
                                        <th>3<br><small>M</small></th>
                                        <th>4<br><small>J</small></th>
                                        <th>5<br><small>V</small></th>
                                        <th>6<br><small>S</small></th>
                                        <th>7<br><small>D</small></th>
                                        <th>8<br><small>L</small></th>
                                        <th>9<br><small>M</small></th>
                                        <th>10<br><small>M</small></th>
                                        <th>11<br><small>J</small></th>
                                        <th>12<br><small>V</small></th>
                                        <th>13<br><small>S</small></th>
                                        <th>14<br><small>D</small></th>
                                        <th>15<br><small>L</small></th>
                                        <th>16<br><small>M</small></th>
                                        <th>17<br><small>M</small></th>
                                        <th>18<br><small>J</small></th>
                                        <th>19<br><small>V</small></th>
                                        <th>20<br><small>S</small></th>
                                        <th>21<br><small>D</small></th>
                                        <th>22<br><small>L</small></th>
                                        <th>23<br><small>M</small></th>
                                        <th>24<br><small>M</small></th>
                                        <th>25<br><small>J</small></th>
                                        <th>26<br><small>V</small></th>
                                        <th>27<br><small>S</small></th>
                                        <th>28<br><small>D</small></th>
                                        <th>29<br><small>D</small></th>
                                        <th>30<br><small>D</small></th>
                                        <th>31<br><small>D</small></th>

                                        <th class="sticky-col-right col-total">Total Horas</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    <tr>
                                        <td class="sticky-col col-dni">12345678</td>
                                        <td class="sticky-col col-legajo">44</td>
                                        <td class="sticky-col col-nombre">García, Ana</td>
                                        <td class="sticky-col col-servicio">Enfermería</td>
                                        <td class="sticky-col col-regimen">44</td>

                                        <td><input type="text" class="day-cell" value="8"></td>
                                        <td><input type="text" class="day-cell" value="8"></td>
                                        <td><input type="text" class="day-cell" value="VAC"></td>
                                        <td><input type="text" class="day-cell" value="VAC"></td>
                                        <td><input type="text" class="day-cell" value="VAC"></td>
                                        <td><input type="text" class="day-cell" value="VAC"></td>
                                        <td><input type="text" class="day-cell" value="VAC"></td>
                                        <td><input type="text" class="day-cell" value="8"></td>
                                        <td><input type="text" class="day-cell" value="8"></td>
                                        <td><input type="text" class="day-cell" value="8"></td>
                                        <td><input type="text" class="day-cell" value=""></td>
                                        <td><input type="text" class="day-cell" value=""></td>
                                        <td><input type="text" class="day-cell" value=""></td>
                                        <td><input type="text" class="day-cell" value=""></td>
                                        <td><input type="text" class="day-cell" value=""></td>
                                        <td><input type="text" class="day-cell" value=""></td>
                                        <td><input type="text" class="day-cell" value=""></td>
                                        <td><input type="text" class="day-cell" value=""></td>
                                        <td><input type="text" class="day-cell" value=""></td>
                                        <td><input type="text" class="day-cell" value=""></td>
                                        <td><input type="text" class="day-cell" value=""></td>
                                        <td><input type="text" class="day-cell" value=""></td>
                                        <td><input type="text" class="day-cell" value=""></td>
                                        <td><input type="text" class="day-cell" value=""></td>
                                        <td><input type="text" class="day-cell" value=""></td>
                                        <td><input type="text" class="day-cell" value=""></td>
                                        <td><input type="text" class="day-cell" value=""></td>
                                        <td><input type="text" class="day-cell" value=""></td>
                                        <td><input type="text" class="day-cell" value=""></td>
                                        <td><input type="text" class="day-cell" value=""></td>
                                        <td><input type="text" class="day-cell" value=""></td>

                                        <td class="sticky-col-right col-total">
                                            <span class="total-horas">40</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="sticky-col col-dni">12345678</td>
                                        <td class="sticky-col col-legajo">44</td>
                                        <td class="sticky-col col-nombre">García, Ana</td>
                                        <td class="sticky-col col-servicio">Enfermería</td>
                                        <td class="sticky-col col-regimen">44</td>

                                        <td><input type="text" class="day-cell" value="8"></td>
                                        <td><input type="text" class="day-cell" value="8"></td>
                                        <td><input type="text" class="day-cell" value="VAC"></td>
                                        <td><input type="text" class="day-cell" value="VAC"></td>
                                        <td><input type="text" class="day-cell" value="VAC"></td>
                                        <td><input type="text" class="day-cell" value="VAC"></td>
                                        <td><input type="text" class="day-cell" value="VAC"></td>
                                        <td><input type="text" class="day-cell" value="8"></td>
                                        <td><input type="text" class="day-cell" value="8"></td>
                                        <td><input type="text" class="day-cell" value="8"></td>
                                        <td><input type="text" class="day-cell" value=""></td>
                                        <td><input type="text" class="day-cell" value=""></td>
                                        <td><input type="text" class="day-cell" value=""></td>
                                        <td><input type="text" class="day-cell" value=""></td>
                                        <td><input type="text" class="day-cell" value=""></td>
                                        <td><input type="text" class="day-cell" value=""></td>
                                        <td><input type="text" class="day-cell" value=""></td>
                                        <td><input type="text" class="day-cell" value=""></td>
                                        <td><input type="text" class="day-cell" value=""></td>
                                        <td><input type="text" class="day-cell" value=""></td>
                                        <td><input type="text" class="day-cell" value=""></td>
                                        <td><input type="text" class="day-cell" value=""></td>
                                        <td><input type="text" class="day-cell" value=""></td>
                                        <td><input type="text" class="day-cell" value=""></td>
                                        <td><input type="text" class="day-cell" value=""></td>
                                        <td><input type="text" class="day-cell" value=""></td>
                                        <td><input type="text" class="day-cell" value=""></td>
                                        <td><input type="text" class="day-cell" value=""></td>
                                        <td><input type="text" class="day-cell" value=""></td>
                                        <td><input type="text" class="day-cell" value=""></td>
                                        <td><input type="text" class="day-cell" value=""></td>

                                        <td class="sticky-col-right col-total">
                                            <span class="total-horas">40</span>
                                        </td>
                                    </tr>
                                </tbody>

                            </table>
                        </div>

                    </div>

                </div>
            </div>
        </div>

    </div>
@endsection
