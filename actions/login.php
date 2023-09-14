<?php
session_start();
include('connect.php');

$username=$_POST['username'];
$idNum=$_POST['idNum'];
$password=$_POST['password'];
$std=$_POST['std'];

$sql="Select * from `userdata` where username='$username'and idNum='$idNum'and password='$password'and standard='$std'";

$result=mysqli_query($con,$sql);
if(mysqli_num_rows($result)>0){
    $sql="Select username,photo,votes,id from`userdata` where standard='group'";
    $resultgroup=mysqli_query($con,$sql);
    if(mysqli_num_rows($result)>0){
       $groups=mysqli_fetch_all($resultgroup,MYSQLI_ASSOC);
       $_SESSION['groups']=$groups;

    }
    $data=mysqli_fetch_array($result);
    $_SESSION['id']=$data['id'];
    $_SESSION['status']=$data['status'];
    $_SESSION['data']=$data;

    echo'<script>
    window.location="../partials/dashboard.php";
    </script>';    

}else{
    echo'<script>
    alert("Invalid credentials");
    window.location="../";
    </script>';
}

?>