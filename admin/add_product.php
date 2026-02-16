<?php
include('header.php');  
?>


        <div class="col-12 col-md-8 col-lg-6 mx-auto" >
            <!-- header  -->
            <div class="alert alert-success text-center" role="alert">  
                <h3>Add Product</h3>
            </div> 
            <!-- header end  -->
             <form action="add_product.php" method="post" class="shadow-lg p-4" enctype="multipart/form-data">
                
             <div class="mb-2">
                    <label for="product_name" class="form-label">Product Name</label>
                    <input type="text" class="form-control"  name="pname" required>
                </div>
                <div class="mb-2">
                    <label for="product_company" class="form-label">Product Company</label>
                    <input type="text" class="form-control"  name="pcompany" required>
                </div>
                <div class="mb-2">
                    <label for="product_price" class="form-label">Product Price</label>
                    <input type="text" class="form-control"  name="pprice" required>
                </div>
                <div class="mb-2">
                    <label for="product_qty" class="form-label">Product Qty</label>
                    <input type="text" class="form-control"  name="pqty" required>
                </div>
                <div class="mb-2">
                    <label for="product_amount" class="form-label">Product Amount</label>
                    <input type="text" class="form-control"  name="pamount" required>
                </div>
                <div class="mb-2">
                    <label for="product_category" class="form-label">Category</label>
                    <select class="form-select" name="pcat">
                        <option value="">Select category</option>
                        <option value="CPU">CPU</option>
                        <option value="Motherboard">Motherboard</option>
                        <option value="RAM">RAM</option>
                        <option value="GPU">GPU</option>
                        <option value="Storage">Storage</option>
                        <option value="PSU">PSU</option>
                        <option value="Case">Case</option>
                        <option value="Cooler">Cooler</option>
                        <option value="Monitor">Monitor</option>
                        <option value="Accessory">Accessory</option>
                    </select>
                </div>
                <div class="mb-2">
                    <label for="product_description" class="form-label">Product Description</label>
                    <textarea class="form-control" id="product_description" name="product_description" rows="3" required></textarea>
                </div>
                <div class="mb-3">
                    <label for="formFile" class="form-label">Upload Product Image</label>
                    <input class="form-control" type="file" id="formFile" name="pimg" required>
                </div>
                <button type="submit" class="btn btn-primary" name="add_product">Add Product</button>
             
                
             </div> 
            </form>
            
        </div>
    </div>
</div>
<?php
if(isset($_POST['add_product'])){
    include('conn.php');
    $pname=$_POST['pname'];
    $pcompany=$_POST['pcompany'];
    $pprice=$_POST['pprice'];
    $pqty=$_POST['pqty'];
    $pamount=$_POST['pamount'];
    $pdescription=$_POST['product_description'];
    $filename=$_FILES["pimg"]["name"];
    $target_dir="../productimg/";
    $target_file=$target_dir.basename($filename);
     $pcat = isset($_POST['pcat']) ? $_POST['pcat'] : '';
            if(move_uploaded_file($_FILES["pimg"]["tmp_name"],$target_file)){
                $sqlq="INSERT INTO `products` ( `pname`, `pcompany`, `pqty`, `pprice`, `pamount`, `pdisc`, `pimg`, `pcat`) VALUES ('$pname', '$pcompany', '$pqty', '$pprice', '$pamount', '$pdescription', '$filename', '$pcat')";
            $result=mysqli_query($con,$sqlq);
    if($result){
        echo "<script>alert('Product Added Successfully');</script>";
        
    }else{
        echo "<script>alert('Product Not Added');</script>";        
    }
    }
    
    
}


?>

<?php
include('footer.php');  
?>