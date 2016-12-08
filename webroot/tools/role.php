<?php
$host = 'localhost';
$user = 'gbm';
$password = 'QDQj86N6x2HjQCE7';
$database = 'gbm';
$conn = mysql_connect( $host, $user, $password );
mysql_select_db( $database, $conn ) or die( mysql_error() );

$sql = "SELECT id, role_name FROM roles WHERE is_active = 1;";
$result = mysql_query( $sql );
while( $row = mysql_fetch_assoc($result) ) {
    $roles[] = $row;
}
if( $_POST) {
    $data = $_POST;
    $sql = "UPDATE users set reload = 1, role_id = '" . $data['role_id'] . "' WHERE id = '" . $data['user_id'] . "';";
    mysql_query( $sql );
    header('Location: '.$_SERVER['PHP_SELF']);
}
$users = [ 
    [ 'id' => 151009, 'name' => 'Gita' ],
    [ 'id' => 151010, 'name' => 'Daljeet' ],
    [ 'id' => 151011, 'name' => 'Minakshi' ],
];
?>
<table border="1" style="width:100%">
    <thead>
        <th>
            Name
        </th>
        <th>
            Role
        </th>
        <th>
            Change Role
        </th>
    </thead>
    <tbody>
        <?php foreach( $users as $user ) { ?>
            <form action="" method="post" name="changeRole">
                <tr>
                    <td>
                        <?php echo $user['name']; ?>
                    </td>
                    <td>
                        <select name="role_id">
                            <?php foreach( $roles as $role ) { ?>
                                <option value="<?php echo $role['id']; ?>">
                                    <?php echo $role['role_name']; ?>
                                </option>
                            <?php } ?>
                        </select>
                    </td>
                    <td>
                        <input type="hidden" value="<?php echo $user['id']; ?>" name="user_id" />
                        <button type="submit">Submit</button>
                    </td>
                </tr>
            </form>
        <?php } ?>
    </tbody>
</table>