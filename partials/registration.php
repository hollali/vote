<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Voting system - page</title>
    <!---Bootstrap css link---> 
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC
    " crossorigin="anonymous">
</head> 
<body class="bg-dark">
    <h1 class="text-center text-info p-3">VOTING SYSTEM</h1>
    <div class="bg-info py-4">
        <h2 class="text-center">REGISTRATION FORM</h2>
        <div class="container text-center">
             <form action="../actions/register.php" method="POST"
             enctype="multipart/form-data">
                <diV class="mb-3">
                    <input type="text"class="form-control w-50 m-auto"
                    placeholder="Enter Your Username"
                    required="required"name="username">
                </diV>
                <diV class="mb-3">
                    <input type="text"class="form-control w-50 m-auto"
                    placeholder="Enter Your Voters ID Number"
                    required="required"name="idNum" maxlength="10">
                </diV>
                <diV class="mb-3">
                    <input type="password"class="form-control w-50 m-auto"
                    placeholder="Enter Your Password"
                    required="required"name="password">
                </diV>
                <diV class="mb-3">
                    <input type="password"class="form-control w-50 m-auto"
                    placeholder=" Confirm Your Password"
                    required="required"name="cpassword">
                </diV>
                <diV class="mb-3">
                    <input type="file"class="form-control w-50 m-auto
                    "name="photo">
                </diV>
                <div class="mb-3">
                    <select name="std" class="form-select w-50 m-auto">    
                        <option value="group">Candidate</option>
                        <option value="voter">Voter</option>
                    </select>  
                 </div>
                 <button type="submit"class="btn btn-dark my-4">Register</a></button>
                 <p>Already have an account? <a href="../" class="text-white"> login here</a></p>
             </form>
        </div>
    </div>
</body>
</html>