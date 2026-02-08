<?php
define('page','myorder');
include('header.php');
$view = isset($_GET['view']) ? trim($_GET['view']) : 'list';
?>
<div class="container">
    <div class="row">
        <div class="col-md-12">
                        <h2>Orders</h2>
                        <div class="mb-3">
                            <?php if($view === 'history'): ?>
                                <a class="btn btn-sm btn-outline-secondary" href="myorder.php">Active Orders</a>
                            <?php else: ?>
                                <a class="btn btn-sm btn-outline-secondary" href="myorder.php?view=history">Order History</a>
                            <?php endif; ?>
                        </div>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Product Name</th>
                        <th>Quantity</th>
                        <th>Price</th>
                        <th>Total Price</th>  
                        <th>Status</th>  
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                                        include('../admin/conn.php');
                                        $sessionUser = $_SESSION['username'] ?? '';
                                        $sessionUserEsc = mysqli_real_escape_string($con, $sessionUser);
                                        $sessionUid = $_SESSION['user_id'] ?? null;
                                        // allow matching either by email/username or by fallback user_<id> used for legacy builds
                                        $possibleUsers = [ $sessionUserEsc ];
                                        if(!empty($sessionUid)) $possibleUsers[] = 'user_'.intval($sessionUid);
                                        $userList = "'".implode("','", array_map(function($v){ return mysqli_real_escape_string($GLOBALS['con'],$v); }, $possibleUsers))."'";
                                        if($view === 'history'){
                                            // show delivered/archive orders from purchase_history if table exists
                                            $db = '';
                                            $rdb = @mysqli_query($con, "SELECT DATABASE() AS dbname");
                                            $show_res = false;
                                            if($rdb && mysqli_num_rows($rdb)>0){ $db = mysqli_fetch_assoc($rdb)['dbname']; }
                                            if($db){
                                                $tbl = mysqli_real_escape_string($con, 'purchase_history');
                                                $qc = @mysqli_query($con, "SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA='".mysqli_real_escape_string($con,$db)."' AND TABLE_NAME='{$tbl}' LIMIT 1");
                                                                                                if($qc && mysqli_num_rows($qc)>0){
                                                                                                                    // show cancelled and delivered orders in user history (match possible user identifiers)
                                                                                                                    $sql = "SELECT * FROM `purchase_history` WHERE `user` IN ({$userList}) AND LOWER(IFNULL(delivery_status,'')) IN ('cancelled','delivered') ORDER BY pdate DESC";
                                                        $show_res = true;
                                                    }
                                            }
                                            if(!$show_res){ $result = false; }
                                            else { $result = mysqli_query($con,$sql); }
                                        } else {
                                            $sql="SELECT * FROM `purchase` WHERE `user` IN ({$userList}) ORDER BY pdate DESC";
                                            $result=mysqli_query($con,$sql);
                                        }
                    if($result){
                    while($row=mysqli_fetch_assoc($result)){
                        echo '<tr>';
                        echo '<td>'.$row['pname'].'</td>';
                        echo '<td>'.$row['qty'].'</td>';
                        echo '<td>'.$row['pprice'].'</td>';
                        $total=$row['pprice'] * $row['qty'];
                        echo '<td>'.$total.'</td>';
                        echo '<td>'.htmlspecialchars($row['status'] ?? $row['delivery_status'] ?? '').'</td>';
                        ?> 
                        <td>
                       <form action="myorder_details.php" method="post">
                        <input type="hidden" name="order_id" value="<?php echo $row['pid']; ?>">
                        <input type="submit"  value="View Details" class="btn btn-info">
                       </form>
                    </td>
                        <?php
                        echo '</tr>';
                    }
                    } else {
                      echo "<tr><td colspan='6' class='text-center small-muted'>No orders found</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
include('footer.php');
?>